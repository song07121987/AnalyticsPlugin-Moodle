<?php

function check_roles_assign($context,$userid,$role)
{
    global $DB;
    $sql = "SELECT count(*) as count
  FROM {role_assignments}
  where contextid= '" . $context . "' and userid = '" . $userid . "' and roleid = '" . $role . "' ";

    $result = $DB->get_field_sql($sql);

    if ($result > 0) {
        return true;
    } else {
        return false;
    }

}

function check_files_ext($fileid) {
    global $DB;
    $sql = "SELECT count(*) as count
  FROM {files_ext}
  where
  fileid = ?";

    $result = $DB->get_field_sql($sql, array($fileid));

    if ($result > 0) {
        return true;
    } else {
        return false;
    }

}

function check_files_version($fileid) {
    global $DB;
    $sql = "SELECT count(*) as count
  FROM {files_version}
  where
  fileid = ?";

    $result = $DB->get_field_sql($sql, array($fileid));

    if ($result > 0) {
        return true;
    } else {
        return false;
    }

}

function hasGlobalRole($userid, $role = 'masteradmin') {
    global $DB;
    $roleid='';
    if (is_siteadmin($userid) &&  $role <> 'limited' )
        //if checking for limited..you dont want to be part of it.
        return 1;
    if ($role == 'masteradmin')
        $roleid = 11;
    else if ($role == 'helpdesk')
        $roleid = 10;
    else if ($role == 'communitymanager')
        $roleid = 13;
    else if ($role == 'limited')
        $roleid = 12;
    $sql = "select count(*) as cnt from {role_assignments} where userid = ? and  roleid = ?";
    $result = $DB->get_field_sql($sql, array($userid, $roleid));
    if ($result == 0)
        return false;
    return true;
}

function hasUnitAdminRole($userid, $categoryid = 0) {
    global $DB;
    if (is_siteadmin($userid)) {
        return 1;
    }
    $shortname = 'unitadmin';
    /****** Check user is Iscategoryadmin ******/
    $sql = "SELECT count(*) as count
            FROM {course_categories} e
            JOIN {course_categories_ext} f  on	 e.id = f.categoryid
            JOIN {context} c on f.categoryid = c.instanceid
            JOIN {role_assignments} ra on ra.contextid = c.id
            JOIN {role} r on r.id = ra.roleid
            WHERE contextlevel= ? and deleted='0' and r.shortname = ?  and userid = ?";
    if ($categoryid != 0) {
        $sql = $sql . " and f.categoryid = ? ";
        $result = $DB->get_field_sql($sql, array(40, $shortname, $userid, $categoryid));
    } else {
        $result = $DB->get_field_sql($sql, array(40, $shortname, $userid));
    }
    if ($result == 0) {
        return false;
    }
    return true;
}

function checkIfOnlySubUnitAdminRole($userid) {
    global $DB;

    if (hasMasterAdminRole($userid) || is_siteadmin($userid)) {
        return false;
    }

    $sql = "Select count(e.id) as count
        FROM {course_categories} e, {course_categories_ext} f, {context} c, {role_assignments} ra, {role} r
        WHERE   e.id            = f.categoryid
        and     f.categorytype  = 'course'
        and     f.deleted         = 0
        and     f.categoryid    = c.instanceid
        and     ra.contextid    = c.id
        and     r.id            = ra.roleid
        and     contextlevel    = 40
        and     r.shortname     = 'unitadmin'
        and     userid          = ?
        and     parent = 0";

    $result = $DB->get_record_sql($sql, array($userid));

    if (hasUnitAdminRole($userid) && $result->count == 0) {
        return true;
    } else {
        return false;
    }

}

function subunit_access_check($userid, $categoryid = 0) {
    global $DB;
    if (is_siteadmin($userid)) {
        return true;
    }

    $sql = '';
    $categorywhere = '';
    $params = [];

    if ($categoryid != 0) {
        $categorywhere = " AND e.id = @categoryid ";
        $sql = "DECLARE @categoryid bigint = ?; ";
        $params[] = $categoryid;
    }

    $params[] = 40;
    $params[] = 'unitadmin';
    $params[] = $userid;

    $sql .= "
            DECLARE @contextlevel bigint = ?
            DECLARE @shortname varchar(MAX) = ?
            DECLARE @userid bigint = ?
            SELECT e.id
            FROM {course_categories} e
            JOIN {course_categories_ext} f on	e.id = f.categoryid
            JOIN {context} c on f.categoryid = c.instanceid
            JOIN {role_assignments} ra on ra.contextid = c.id
            JOIN {role} r on r.id = ra.roleid
            WHERE contextlevel= @contextlevel and deleted='0' and r.shortname = @shortname AND ra.userid = @userid $categorywhere
            UNION
            SELECT e.id
            FROM {course_categories} e
            JOIN {course_categories_ext} ce ON ce.categoryid = e.id
            JOIN {course_categories} pc ON pc.id = e.parent
            JOIN (
                SELECT con.instanceid
                FROM {role_assignments} ra
                JOIN {context} con ON con.id = ra.contextid
                JOIN {role} r ON r.id = ra.roleid
                WHERE ra.userid = @userid AND r.shortname = @shortname
                AND con.contextlevel = @contextlevel
            ) a ON a.instanceid = pc.id $categorywhere
            WHERE ce.deleted='0' AND e.id = @categoryid";

    return $DB->record_exists_sql($sql, $params);
}

function hasCourseManagerRole($userid) {
    global $DB;
    $data = array(
        "manager",
        "coursecreator",
        "unitadmin",
        "masteradmin"
    );


    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasCourseAdminRole($userid) {
    global $DB;
    $data = array(
        "coursecreator",
        "unitadmin",
        "masteradmin"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasInstructorRole($userid) {
    global $DB;
    $data = array(
        "coursecreator",
        "editingteacher",
        "teacher",
        "unitadmin",
        "masteradmin"
    );

    $result = filterRole($userid, $data);

    if ($result > 0) {
        return true;
    }
    return false;
}

function isLeapInstructor($userid) {
    global $DB;
    $instructorroleid = 3;

    $sql = "SELECT DISTINCT c.id, c.format from {course} c LEFT JOIN {context} con on c.id = con.instanceid LEFT JOIN 
        {role_assignments} ra on ra.contextid = con.id 
        where con.contextlevel = 50 and ra.userid = ?";

    $records = $DB->get_records_sql($sql, array($userid));

    foreach ($records as $record) {
        if (strcasecmp($record->format, 'leap') == 0) {
            return true;
        }
    }

}

function hasCourseRole($userid, $courseid, $role) {
    global $DB;
    if (is_siteadmin($userid)) {
        return 1;
    }
    $sql = "";
    if ($courseid != 0) {
        $sql .= "SELECT * from (";
    }
    $sql .= "SELECT count(c.id) as count
            FROM {course} c
            JOIN {course_options2} co  on c.id = co.courseid and co.deleted = 0
            JOIN {context} ct on c.id = ct.instanceid and ct.contextlevel = 50
            JOIN {role_assignments} ra on ra.contextid = ct.id
            JOIN {role} r on r.id = ra.roleid and r.shortname = ?
            WHERE userid = ?";
    if ($courseid != 0) {
        $sql = $sql . " and c.id = ? ";
        $sql = $sql . " UNION SELECT count (c.id) as count
                FROM {course} c
                JOIN {course_options2} co on c.id = co.courseid and co.deleted = 0 where c.id = ? and co.parentcourse in (
                    SELECT c.id
                    FROM {course} c
                    JOIN {course_options2} co  on c.id = co.courseid and co.deleted = 0
                    JOIN {context} ct on c.id = ct.instanceid and ct.contextlevel = 50
                    JOIN {role_assignments} ra on ra.contextid = ct.id
                    JOIN {role} r on r.id = ra.roleid and r.shortname = ?
                    WHERE userid = ?
                    ) ";
        if (strcasecmp($role, 'unitadmin') == 0) {
            $sql .= " UNION
            SELECT count(y.id) from (
                SELECT c.id
                FROM {course} c
                JOIN {course_options2} co on c.id = co.courseid and co.deleted = 0
                JOIN {course_categories} f on f.id = c.category
                where c.id = " . $courseid . " and c.category in (
                    SELECT c.id
                    FROM {course_categories} c
                    JOIN {context} ct on c.id = ct.instanceid and ct.contextlevel = 40
                    JOIN {role_assignments} ra on ra.contextid = ct.id
                    JOIN {role} r on r.id = ra.roleid and r.shortname = 'unitadmin'
                    WHERE userid = " . $userid . "
                    )
                or f.parent in (
                    SELECT c.id
                    FROM {course_categories} c
                    JOIN {context} ct on c.id = ct.instanceid and ct.contextlevel = 40
                    JOIN {role_assignments} ra on ra.contextid = ct.id
                    JOIN {role} r on r.id = ra.roleid and r.shortname = 'unitadmin'
                    WHERE userid = " . $userid . "
                    )
            ) y where y.id = " . $courseid . "
                    ";
        }
        $sql = $sql . ") x where x.count != 0";
        $result = $DB->get_field_sql($sql, array($role, $userid, $courseid, $courseid, $role, $userid));
    } else {
        $result = $DB->get_field_sql($sql, array($role, $userid));
    }
    if (!$result || $result == 0) {
        return false;
    }
    return true;
}

function hasCourse($userid, $courseid) {
    global $DB;
    if (is_siteadmin($userid)) {
        return true;
    }
    $sql = "SELECT count(c.id) as count
        FROM {role_assignments} r, {context} t, {course} c, {course_options2} e
        WHERE r.userid = ? and c.id = ? and r.contextid = t.id and t.contextlevel = 50 and t.instanceid = c.id and c.id = e.courseid and e.deleted = 0 and e.deleted = 0 and e.coursetype in ('Meta','Child', 'Community')";
    $result = $DB->get_field_sql($sql, array($userid, $courseid));
    if (!$result || $result == 0) {
        return false;
    }
    return true;
}

function hasStudentOnlyRole($userid) {
    global $DB;
    $data = array(
            "coursecreator",
            "editingteacher",
            "teacher",
            "unitadmin",
            "helpdesk",
            "masteradmin",
            "communitymanager",
            "etadmin",
        );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return false;
    }
    return true;
}

function hasAdminRole($userid) {
    global $DB;
    $data = array(
        "unitadmin",
        "masteradmin"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasMasterAdminRole($userid) {
    global $DB;
    $data = array(
        "masteradmin"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasAdminAndInstructor($userid) {
    global $DB;
    $data = array(
        "editingteacher",
        "unitadmin",
        "masteradmin"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasAdminAndHelpDeskRole($userid) {
    global $DB;
    $data = array(
        "unitadmin",
        "helpdesk",
        "masteradmin"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasHelpDeskRole($userid) {
    global $DB;
    $data = array(
        "helpdesk"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function hasCommunityManagerRole($userid) {
    global $DB;
    $data = array(
        "unitadmin",
        "masteradmin",
        "communitymanager"
    );

    $result = filterRole($userid, $data);
    if ($result > 0) {
        return true;
    }
    return false;
}

function filterRole($userid, $data) {
    global $DB, $debugmsg, $messageobj;
    $sql = "SELECT id, shortname FROM {role}";
    $data4 = $DB->get_records_sql($sql);
    $data2 = array();
    foreach ($data4 as $item4) {
        array_push($data2, $item4->shortname);
    }
    $data3 = $data2;
    $data3 = array_fill_keys($data3, 0);
    foreach ($data2 as $removelist) {
        foreach ($data as $includelist) {
            if ($includelist != $removelist) {
                $data3[$includelist] = '1';
            }
        }
    }
    foreach ($data4 as $item4) {
        foreach ($data3 as $key => $value) {
            if ($item4->shortname == $key && $value == 0) {
                if (!isset($filter)) {
                    $filter = '(' . $item4->id;
                } else {
                    $filter = $filter . ',' . $item4->id;
                }
            }
        }
    }
    if (isset($filter)) {
        $filter = $filter . ')';
    }
    $sql = "SELECT count(*) as count
            FROM {role_assignments} ra
            WHERE userid = ? and roleid not in " . $filter;
    $result = $DB->get_field_sql($sql, array($userid));
    if (is_siteadmin($userid)) {
        $result = 1;
    }
    if ($result > 0) {
        return true;
    }
    return false;
}

function print_admin_header_title($title) {
    echo '  <div class="mainheader admin-user-header">
			    <h1>' . $title . '</h1>
		    </div>';
}

function print_admin_subnav() {
    global $CFG;
    $level1 = "Units";
    if (isset($CFG->mq_admin_courselevel1name)) {
        $level1 = $CFG->mq_admin_courselevel1name;
    }
    $level2 = "";
    if (isset($CFG->mq_admin_courselevel2name)) {
        $level2 = $CFG->mq_admin_courselevel2name;
    }
    $metaname = "Meta Courses";
    if (isset($CFG->mq_admin_metacoursename)) {
        $metaname = $CFG->mq_admin_metacoursename;
    }
    $crname = "Course Runs";
    if (isset($CFG->mq_admin_courserunname)) {
        $crname = $CFG->mq_admin_courserunname;
    }
    $community = "Communities";
    if (isset($CFG->mq_global_communitynameplural)) {
        $community = $CFG->mq_global_communitynameplural;
    }

    echo '	<div class="col-md-12 contentnav admin-user-contentnav">
			  <ul>
                <li><a href="' . $CFG->wwwroot . '/administration/index.php">Dashboard</a></li>
                <li><a href="' . $CFG->wwwroot . '/administration/users.php">Users</a></li>
                <li><a href="' . $CFG->wwwroot . '/administration/units.php">' . $level1 . '</a></li>';
    if ($level2 != '') {
        echo '	<li><a href="' . $CFG->wwwroot . '/administration/units.php?level=2">' . $level2 . '</a></li>';
    }
    echo '		<li><a href="' . $CFG->wwwroot . '/administration/metacourses.php">' . $metaname . '</a></li>
                <li><a href="' . $CFG->wwwroot . '/administration/courseruns.php">' . $crname . '</a></li>';
    if (isset ($CFG->mq_global_communities) && $CFG->mq_global_communities == 1) {
        echo '
                <li><a href="' . $CFG->wwwroot . '/administration/communities.php">' . $community . '</a></li>';
    }
    echo '
                <li><a href="' . $CFG->wwwroot . '/administration/audit.php">Audit Trail</a></li>
              </ul>
			</div>';
}

function print_admin_breadcrumb($menuitem, $menulink) {
    global $CFG;
    echo '  <div class="col-md-6 admin-user-breadcrumb" style="float:right">
				<ol class="breadcrumb">
					<li><a href="' . $CFG->wwwroot . '"><i class="fa fa-dashboard"></i> LMS</a></li>
					<li><a href="' . $CFG->wwwroot . '/administration/">Administration</a></li>
					<li><a href="' . $CFG->wwwroot . '/administration/' . $menulink . '">' . $menuitem . '</a></li>
				</ol>
			</div>';
}

function print_admin_unit_breadcrumb($menuitem, $menulink) {
    global $CFG;
    echo '  <div class="col-md-6 admin-user-breadcrumb" style="float:right">
                <ol class="breadcrumb">
                    <li><a href="' . $CFG->wwwroot . '"><i class="fa fa-dashboard"></i> LMS</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Administration</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Unit</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/' . $menulink . '">' . $menuitem . '</a></li>
                </ol>
            </div>';
}

function print_admin_metacourse_breadcrumb($menuitem, $menulink) {
    global $CFG;
    echo '  <div class="col-md-6 admin-user-breadcrumb" style="float:right">
                <ol class="breadcrumb">
                    <li><a href="' . $CFG->wwwroot . '"><i class="fa fa-dashboard"></i> LMS</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Administration</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Meta Courses</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/' . $menulink . '">' . $menuitem . '</a></li>
                </ol>
            </div>';
}

function print_admin_courserun_breadcrumb($menuitem, $menulink) {
    global $CFG;
    echo '  <div class="col-md-6 admin-user-breadcrumb" style="float:right">
                <ol class="breadcrumb">
                    <li><a href="' . $CFG->wwwroot . '"><i class="fa fa-dashboard"></i> LMS</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Admin</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Communities</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/' . $menulink . '">' . $menuitem . '</a></li>
                </ol>
            </div>';
}

function print_admin_communities_breadcrumb($menuitem, $menulink) {
    global $CFG;
    echo '  <div class="col-md-6 admin-user-breadcrumb" style="float:right">
                <ol class="breadcrumb">
                    <li><a href="' . $CFG->wwwroot . '"><i class="fa fa-dashboard"></i> LMS</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Admin</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/">Course Run</a></li>
                    <li><a href="' . $CFG->wwwroot . '/administration/' . $menulink . '">' . $menuitem . '</a></li>
                </ol>
            </div>';
}

function print_admin_heading($heading) {
    echo '<section class="admin-user-content-header">
                <div class="row">
                    <div class="col-md-12 admin-main-content-heading" style="float:left">
                        <h3>' . clean_text($heading, PARAM_CLEANHTML) . '</h3>
                    </div>
                </div>
          </section>';
}

function admin_display_message($message, $messageobj, $alert = '0') {
    $debug = 0;

    if (isset($message) || $debug == 1) {

        switch ($alert) {
            case '0':
                echo '<div class="alert alert-success" role="alert">';
                break;
            case '1':
                echo '<div class="alert alert-info" role="alert">';
                break;
            case '2':
                echo '<div class="alert alert-warning" role="alert">';
                break;
            case '3':
                echo '<div class="alert alert-danger" role="alert">';
                break;
            default:
                echo '<div class="alert alert-success" role="alert">';
        }
        echo $message;
        if (isset ($messageobj)) {
            if ($debug == 1) {
                echo '<label class="control-label col-sm-12 col-lg-12"><pre>' . print_r($messageobj, true)
                     . '</pre></label>';
            }
        }
        echo '</div>';
    }
}

function admin_display_jobdetails($messageobj) {
    if (isset($messageobj)) {
        echo '<a href="jobdetails.php?id=' . $messageobj->id . '">Please click here to view the job details.</a>';
    }
}

function print_admin_dashboardmenu($highlight) {
    global $USER, $CFG;

    $printhtml = "";

    $quicklinks = "";
    if (hasAdminAndHelpDeskRole($USER->id) || hasCourseAdminRole($USER->id)) {
        $quicklinks .= '<li><a href="users.php"> List Users </a></li>';
    }

    if (hasAdminRole($USER->id) || hasCourseAdminRole($USER->id)) {
        $quicklinks .= '<li><a href="user-add.php"> Create User </a></li>';
    }

    if (hasGlobalRole($USER->id, 'masteradmin')) {
        $quicklinks .= '<li><a href="unit-add.php"> Create Unit </a></li>';
    }

    if (hasAdminRole($USER->id)) {
        $quicklinks .= '<li><a href="metacourse-add.php"> Create Meta Course </a></li>';
    }

    if (hasHelpDeskRole($USER->id)) {
        $quicklinks .= '	<li><a href="user-lockout.php"> Manage User Lockout </a></li>';
    }

    if ($quicklinks != "") {
        $printhtml .= '<div class="col-xs-12 col-md-3 users-menu-area admin-db-stat-menu">';
        $printhtml .= '<div class="user-menu-area-head">
                                    <h4>Quick Links</h4>
                                </div>
                                <nav>
                                    <ul>';
        $printhtml .= $quicklinks;
        $printhtml .= ' </ul ></nav >';
        $printhtml .= '</div>';
    }

    return $printhtml;
}

function print_admin_usermenu($highlight) {
    global $USER;
    echo '<div class="col-md-3 users-menu-area">
            <div class="user-menu-area-head"><h4>Menu</h4></div>
                <nav><ul> ';
    if (hasAdminRole($USER->id) || hasCourseAdminRole($USER->id)) {
        echo '<li><a href = "users.php">List Users</a></li>';
    }

    if (hasAdminRole($USER->id) || hasCourseAdminRole($USER->id)) {
        echo '
                                        <li ><a href = "user-add.php" > Create User </a ></li >
										<li ><a href = "user-upload.php" > Create Multiple Users </a ></li >
';
    }

    echo '<li><a href = "changepassword.php">Change My Password</a ></li>';

    // Commented out as this is not required in Learnet anymore
    /*
    if (hasHelpDeskRole($USER->id) || hasAdminRole($USER->id)) {
        echo '<li><a href = "user-resetpasswd.php">Reset User Password</a></li>
        <li><a href = "user-bulkresetpasswd.php">Reset Multiple User Password</a></li>
        <li><a href = "user-lockout.php">Manage User Lockout</a></li>
        <li><a href = "user-bulklockout.php">Manage Multiple User Lockout</a></li>';
    }
    */

    if (hasGlobalRole($USER->id, 'masteradmin')) {
        global $CFG;
        if (isset ($CFG->mq_global_communities) && $CFG->mq_global_communities == 1) {
            echo '<li><a href = "user-communitymgr.php">Manage Community Managers</a></li>';
        }
        echo '<li><a href = "user-etadmin.php?role=1">Manage Enterprise Tool Creator</a></li>';
    }
    echo '</ul></nav>
          </div> ';
}

function print_student_usermenu($highlight) {
    echo '                <div class="col-md-3 users-menu-area" >
                                <div class="user-menu-area-head" >
                                    <h4 > User Menu </h4 >
                                </div >
                                <nav >
                                    <ul >
                                        <li ><a href = "changepassword.php" > Change My Password </a ></li >
                                    </ul >
                                </nav >
                            </div > ';
}

function print_admin_unitmenu($level, $category = -1) {
    global $CFG, $USER;
    $prefix = '';
    if ($level == 1) {
        $level1 = "Units";
        $level1single = "Unit";
        if (isset($CFG->mq_admin_courselevel1name)) {
            $level1single = $CFG->mq_admin_courselevel1name;
            if ($level1single == 'Programme') {
                $level1 = "Programmes";
            }
        }
    } else {
        $level1 = "Sub-units";
        $level1single = "Sub-units";
        if (isset($CFG->mq_admin_courselevel2name)) {
            $level1single = $CFG->mq_admin_courselevel2name;
            if ($level1single == 'Cluster') {
                $level1 = "Clusters";
            }
        }
        $prefix = '?level=' . $level;
    }
    echo ' <div class="col-xs-12 col-md-3 users-menu-area create-unit-menu-area" >
    							<div class="user-menu-area-head" >
    								<h4 > Menu </h4 >
    							</div >
    							<nav >
    								<ul > ';
    if (hasAdminRole($USER->id)) {
        echo '
            <li ><a href = "units.php' . $prefix . '" > List ' . $level1 . ' </a ></li >
        ';
        if ($level == 1) {
            echo '
                <li ><a href = "units.php?level=2' . $prefix . '" > List Sub-' . $level1 . ' </a ></li >
            ';
        }
    }

    if ($level != 1 && hasAdminRole($USER->id) && !checkIfOnlySubUnitAdminRole($USER->id)) {
        echo '
        <li ><a href = "unit-add.php?level=2" > Create ' . $level1single . ' </a ></li >';
    } else {
        if (hasGlobalRole($USER->id, 'masteradmin')) {
            echo '
                <li ><a href = "unit-add.php' . $prefix . '" > Create ' . $level1single . ' </a ></li >
                <li ><a href = "unit-add.php?level=2" > Create Sub-' . $level1single . ' </a ></li >
';
        }
    }

    echo '  						</ul >
    							</nav >
    						</div > ';
}

function print_admin_metacoursemenu($highlight) {
    global $USER, $CFG;
    $metaname = "Meta Courses";
    if (isset($CFG->mq_admin_metacoursename)) {
        $metaname = $CFG->mq_admin_metacoursename;
    }
    echo '                <div class="col-xs-12 col-md-3 users-menu-area create-unit-menu-area" >
                                <div class="user-menu-area-head" >
                                    <h4 >  Menu </h4 >
                                </div >
                                <nav >
                                    <ul >
                                        <li ><a href = "metacourses.php" > List ' . $metaname . ' </a ></li >
';
    if (hasAdminRole($USER->id)) {
        echo '
                                        <li ><a href = "metacourse-add.php" > Create ' . $metaname . ' </a ></li >
';
    }
    echo '
                                     </ul >
                                </nav >
                            </div > ';
}

function print_admin_communities_menu($highlight) {
    global $CFG, $USER;
    $communityname = "Community";
    $communitynameplural = "Communities";
    if (isset ($CFG->mq_global_communityname)) {
        $communityname = $CFG->mq_global_communityname;
        $communitynameplural = $CFG->mq_global_communitynameplural;
    }
    echo '                <div class="col-xs-12 col-md-3 users-menu-area create-unit-menu-area" >
                                <div class="user-menu-area-head" >
                                    <h4 >  Menu </h4 >
                                </div >
                                <nav >
                                    <ul >';
    if (hasCommunityManagerRole($USER->id)) {
        if (hasMasterAdminRole($USER->id)) {
            echo '<li ><a href = "communities.php" > List Categories </a ></li >';
            echo '<li ><a href = "community-createcat.php" > Create Category </a ></li >';
        }
        echo '                              <li ><a href = "community-list.php" > List ' . $communitynameplural . ' </a ></li >
                                            <li ><a href = "community-create.php" > Create ' . $communityname . ' </a ></li >';
    }
    echo '                         </ul >
                                </nav >
                            </div > ';
}

function print_admin_courserun_menu($highlight)
{
    global $CFG;
    $runname = "Course Runs";
    if (isset($CFG->mq_admin_courserunname))
        $runname = $CFG->mq_admin_courserunname;
    if ($runname == 'Class') $runname = 'Classes';
    echo ' <div class="col-xs-12 col-md-3 users-menu-area create-unit-menu-area" >
                                <div class="user-menu-area-head" >
                                    <h4 >  Menu </h4 >
                                </div >
                                <nav >
                                    <ul >
                                        <li ><a href = "courseruns.php" > List ' . $runname . ' </a ></li >
                                        <li ><a href = "course-upload.php" > Assign Multiple Users </a ></li >
                                    </ul >
                                </nav >
                            </div > ';
}

/* Dashboard Functions */
function getDashboardCounts($userid = null, $pastweek = '', $categoryid = 0) {
    global $DB, $CFG;;

    if ($userid === null) { // Get all directly from db
        $record = new object;
        $record->users = getUsersCount();
        $record->units = getCategoryCount('Course');
        $record->courses = getCourseCount('Meta');
        $record->courseruns = getCourseCount('Child');
        if ($pastweek != '') {
            $record->pusers = getUsersCount($userid, $pastweek);
            $record->punits = getCategoryCount('Course', 1, null, $pastweek);
            $record->pcourses = getCourseCount('Meta', 1, null, $pastweek);
            $record->pcourseruns = getCourseCount('Child', 1, null, $pastweek);
        }
        if (isset ($CFG->mq_global_communities) && $CFG->mq_global_communities == 1) {
            $record->communitycat = getCategoryCount('Community');
            $record->communities = getCourseCount('Community');
            $record->count = 6;
        } else {
            $record->count = 4;
        }

        return $record;
    } else if (is_numeric($userid) && $userid > 0) {    // User id was specified
        $count = 0;
        $record = new object;
        $record->users = null;
        $record->units = null;
        $record->courses = null;
        $record->courseruns = null;
        $record->communitycat = null;
        $record->communities = null;

        // People with higher tier access will be able to view items in lower tiers
        $hasTier1Access = hasAdminRole($userid);   // Master admin and unit admin

        // Tier 2 access: Tier 1 and / or Course Manager
        $hasTier2Access = $hasTier1Access;
        if (!$hasTier1Access) { // Admins already have access
            $hasTier2Access = filterRole($userid, array('coursecreator'));
        }

        // Tier 3 access: Tier 2 and / or instructor
        $hasTier3Access = $hasTier2Access;
        if (!$hasTier2Access) { // Admins and course manager already have access
            $hasTier3Access = filterRole($userid, array('editingteacher')) && isLeapInstructor($userid);
        }

        // Community Tier access: Tier 1 and / or community creator
        $hasCommunityTierAccess = $hasTier1Access;
        if (!$hasCommunityTierAccess) { // Admins already have access
            $hasCommunityTierAccess = hasGlobalRole($userid, 'communitymanager');
        }

        if ($hasTier1Access) {
            $record->units = getCategoryCount('Course', 1, $userid);
            if ($pastweek != '') {
                $record->punits = getCategoryCount('Course', 1, null, $pastweek);
            }
            if ($record->units != null) {
                $count++;
            }
        }

        if (hasHelpDeskRole($userid)) {
            $record->users = getUsersCount($userid);
            if ($pastweek != '') {
                $record->pusers = getUsersCount($userid, $pastweek);
            }
            if ($record->users != null) {
                $count++;
            }
        }

        if ($hasTier2Access) {
            $record->users = getUsersCount($userid);
            if ($pastweek != '') {
                $record->pusers = getUsersCount($userid, $pastweek);
            }
            if ($record->users != null) {
                $count++;
            }
            $record->courses = getCourseCount('Meta', 1, $userid, '', $categoryid);
            if ($pastweek != '') {
                $record->pcourses = getCourseCount('Meta', 1, null, $pastweek, $categoryid);
            }
            if ($record->courses != null) {
                $count++;
            }
        }
        if ($hasTier3Access) {
            $record->courseruns = getCourseCount('Child', 1, $userid, '', $categoryid);
            if ($pastweek != '') {
                $record->pcourseruns = getCourseCount('Child', 1, null, $pastweek, $categoryid);
            }
            if ($record->courseruns != null) {
                $count++;
            }
        }
        if (isset ($CFG->mq_global_communities) && $CFG->mq_global_communities == 1) {
            if ($hasCommunityTierAccess) {

                if (hasMasterAdminRole($userid)) {
                    $record->communitycat = getCategoryCount('Community', 1, $userid);
                    if ($record->communitycat != null) {
                        $count++;
                    }
                }
                $record->communities = getCourseCount('Community', 1, $userid);
                if ($record->communities != null) {
                    $count++;
                }
            }
        }
        $record->count = $count;
        return $record;
    } else {    // Invalid userid was provided
        return null;
    }
}

function getUsersCount($userid = null, $past = '') {
    global $DB;

    $dateqry = '';
    if ($past != '') {
        $dateqry .= ' and timecreated > ' . $past . ' ';
    }

    if ($userid === null) {
        $sql = "SELECT COUNT(*) as cnt FROM {user} WHERE deleted = '0' and id > 2 " . $dateqry;
        return $DB->get_field_sql($sql);
    } else {
        if (hasAdminAndHelpDeskRole($userid)
            || hasCourseAdminRole($userid)) {   // Only admins, unit admins and course managers (roleid = 2) can see all users
            return getUsersCount(null, $past); // Get all users
        } else {    // Normal user
            return null;
        }
    }
}

function getCategoryCount($cattype, $visible = 1, $userid = null, $past = '') {
    global $DB;

    $dateqry = '';
    if ($past != '') {
        $dateqry .= ' and c.timemodified > ' . $past . ' ';
    }

    if ($userid === null) {
        $sql = "SELECT COUNT(*) as cnt FROM {course_categories} c, {course_categories_ext} e WHERE c.visible = ? and c.id = e.categoryid and e.categorytype = ? "
            . $dateqry;
        return $DB->get_field_sql($sql, array($visible, $cattype));
    } else {
        if (hasGlobalRole($userid, 'masteradmin')) {
            return getCategoryCount($cattype, $visible, null, $past);    // Admins get all categories
        } else if ((strcasecmp($cattype, 'Community') == 0) && hasCommunityManagerRole($userid)) {
            return getCategoryCount($cattype, $visible, null, $past);    // Admins get all categories
        } else {    // Get count for categories users have a role assigned in
            $sql = "SELECT COUNT(*) cnt
                    FROM (
                        SELECT c.id
                        FROM {course_categories} c
                        JOIN {course_categories_ext} ce ON ce.categoryid = c.id
                        JOIN (
                            SELECT con.instanceid
                            FROM {role_assignments} ra
                            JOIN {context} con ON con.id = ra.contextid
                            WHERE ra.userid = ?
                            AND con.contextlevel = 40
                        ) a ON a.instanceid = c.id
                        WHERE c.visible = ?
                        AND ce.categorytype = ? $dateqry
                        UNION
                        SELECT c.id
                        FROM {course_categories} c
                        JOIN {course_categories_ext} ce ON ce.categoryid = c.id
                        JOIN {course_categories} pc ON pc.id = c.parent
                        JOIN (
                            SELECT con.instanceid
                            FROM {role_assignments} ra
                            JOIN {context} con ON con.id = ra.contextid
                            WHERE ra.userid = ?
                            AND con.contextlevel = 40
                        ) a ON a.instanceid = pc.id
                        WHERE c.visible = ?
                        AND ce.categorytype = ? $dateqry
                    ) q";
            return $DB->get_field_sql($sql, [$userid, $visible, $cattype, $userid, $visible, $cattype]);
        }
    }
}

function getCourseCount($ctype, $visible = 1, $userid = null, $past = '', $categoryid = 0) {
    global $DB, $USER;

    $dateqry = '';
    $categoryqry = '';
    if ($past != '') {
        $dateqry .= ' and e.timecreated > ' . $past . ' ';
    }
    if ($categoryid > 0) {
        $categoryqry .= ' and e.category = ' . $categoryid;
    }

    if ($userid === null) {
        $ownerid = "";
        if (strcasecmp($ctype, 'Community') == 0 && !hasMasterAdminRole($USER->id)) {
            $ownerid = " AND o.ownerid = " . $USER->id;
        }
        $sql = "SELECT COUNT(*) as cnt FROM {course} e, {course_options2} o WHERE e.visible = ? and e.id = o.courseid and o.coursetype = ? and o.deleted = 0 "
            . $dateqry . $categoryqry . $ownerid;
        return $DB->get_field_sql($sql, array($visible, $ctype));
    } else {
        if (hasGlobalRole($userid, 'masteradmin')) {
            return getCourseCount($ctype, $visible, null, $past, $categoryid);    // Admins get all courses
        } else if ((strcasecmp($ctype, 'Community') == 0) && hasCommunityManagerRole($userid)) {
            return getCourseCount($ctype, $visible, null, $past, $categoryid);    // Admins get all categories
        } else {
            if (strcasecmp($ctype, 'meta') == 0) {
                $sql = "SELECT COUNT(*) cnt
                FROM (
                    SELECT DISTINCT e.id
                    FROM {course} e ,{course_options2} o,{course_categories} f
                    WHERE   e.id            = o.courseid
                    and     f.id            = e.category
                    and     o.coursetype    ='meta'
                    and     deleted         = 0
                    and (( e.id in
                        (select distinct c.id
                        from {course} c, {role_assignments} ra, {context} t
                        where ra.contextid      = t.id
                        and t.contextlevel      = 50
                        and t.instanceid        = c.id
                        and userid              = ?
                        ))
                        or( e.category  in
                        (select distinct e.id
                        from {course_categories} e, {role_assignments} ra, {context} t
                        where ra.contextid      = t.id
                        and t.contextlevel      = 40
                        and t.instanceid        = e.id
                        and userid              = ?
                        ))
                        or( f.parent  in
                        (select distinct e.id
                        from {course_categories} e, {role_assignments} ra, {context} t
                        where ra.contextid      = t.id
                        and t.contextlevel      = 40
                        and t.instanceid        = e.id
                        and userid              = ?
                        ))
                        )
                    " . $dateqry . $categoryqry . "
                ) ac";
                return $DB->get_field_sql($sql, array($userid, $userid, $userid));
            } else if (strcasecmp($ctype, 'child') == 0) {
                $sql = "SELECT COUNT(*) cnt
                FROM (
                    SELECT DISTINCT e.id
                    FROM {course} e ,{course_options2} o,{course} f
                    WHERE e.id = o.courseid
                    AND f.id = o.parentcourse
                    AND o.coursetype ='child'
                    AND deleted = 0
                    AND((e.id IN
                        (SELECT DISTINCT c.id
                         FROM {course} c, {role_assignments} ra, {context} t
                         WHERE ra.contextid = t.id
                           AND t.contextlevel = 50
                           AND t.instanceid = c.id
                           AND userid = ?
                           AND ra.roleid NOT IN (5,6,7) ))
                    OR(o.parentcourse IN
                        (SELECT DISTINCT c.id
                         FROM {course} c, {role_assignments} ra, {context} t
                         WHERE ra.contextid = t.id
                         AND t.contextlevel = 50
                         AND t.instanceid = c.id
                         AND userid = ? ))
                    OR(e.category IN
                        (SELECT DISTINCT e.id
                         FROM {course_categories} e, {role_assignments} ra, {context} t
                         WHERE ra.contextid = t.id
                         AND t.contextlevel = 40
                         AND t.instanceid = e.id
                         AND userid = ? ))
                    OR(e.category IN
                        (SELECT id
                         FROM {course_categories} g
                         WHERE g.parent IN
                            (SELECT DISTINCT e.id
                             FROM {course_categories} e, {role_assignments} ra, {context} t
                             WHERE ra.contextid = t.id
                             AND t.contextlevel = 40
                             AND t.instanceid = e.id
                             AND userid = ? ))
                        ))
                    " . $dateqry . $categoryqry . "
                ) ac";
                return $DB->get_field_sql($sql, array($userid, $userid, $userid, $userid));
            } else if (strcasecmp($ctype, 'community') == 0) {
                $sql = "SELECT COUNT(*) cnt
                FROM (
                    SELECT DISTINCT e.id
                    FROM {course} e ,{course_options2} o,{course} f
                    WHERE e.id = o.courseid
                    AND f.id = o.parentcourse
                    AND o.coursetype ='community'
                    AND deleted = 0
                    AND((e.id IN
                        (SELECT DISTINCT c.id
                         FROM {course} c, {role_assignments} ra, {context} t
                         WHERE ra.contextid = t.id
                           AND t.contextlevel = 50
                           AND t.instanceid = c.id
                           AND userid = ?
                           AND ra.roleid NOT IN (5,6,7) ))
                    OR(o.parentcourse IN
                        (SELECT DISTINCT c.id
                         FROM {course} c, {role_assignments} ra, {context} t
                         WHERE ra.contextid = t.id
                         AND t.contextlevel = 50
                         AND t.instanceid = c.id
                         AND userid = ? ))
                    OR(e.category IN
                        (SELECT DISTINCT e.id
                         FROM {course_categories} e, {role_assignments} ra, {context} t
                         WHERE ra.contextid = t.id
                         AND t.contextlevel = 40
                         AND t.instanceid = e.id
                         AND userid = ? ))
                    OR(e.category IN
                        (SELECT id
                         FROM {course_categories} g
                         WHERE g.parent IN
                            (SELECT DISTINCT e.id
                             FROM {course_categories} e, {role_assignments} ra, {context} t
                             WHERE ra.contextid = t.id
                             AND t.contextlevel = 40
                             AND t.instanceid = e.id
                             AND userid = ? ))
                        ))
                    " . $dateqry . $categoryqry . "
                ) ac";
                return $DB->get_field_sql($sql, array($userid, $userid, $userid, $userid));
            }
        }
    }
}

function checkImageFile($imagefile) {
    global $DB, $USER;

    $draftfiles = $DB->get_records('files', array('itemid'    => $imagefile,
                                                  'contextid' => context_user::instance($USER->id)->id), 'filename, mimetype');
    if (empty($draftfiles)) {
        return true;
    }
    foreach ($draftfiles as $draftfile) {
        if ($draftfile->mimetype != null) {
            if (strpos($draftfile->mimetype, 'image') === false) {
                return false;
            }
        }
    }
    return true;
}

function getUserUnit($userid) {
    global $DB;

    $sql = "SELECT c.name as unit
        FROM {role_assignments} r, {context} t, {course_categories} c, {course_categories_ext} e
        WHERE r.userid = ? and r.contextid = t.id and t.contextlevel = 40 and t.instanceid = c.id and c.id = e.categoryid and e.categorytype = 'Course'";

    $results = $DB->get_fieldset_sql($sql, array($userid));
    if ($results) {
        return $results;
    } else {
        return array();
    }
}

// Enroll the user into a course in a specific role.
function enrol_user_to_course($courseId, $roleShortname, $userId) {
    global $DB;

    $context = context_course::instance($courseId);

    //To enrol community manager as moderator. (because community manager are already enrolled.)
    if (is_enrolled($context, $userId)) {
        $skip = 1;
        $course = $DB->get_record('course', array('id' => $courseId), '*', MUST_EXIST);
        if ($course->format == 'socialwall') {
            $skip = 0;
        }
    } else {
        $skip = 0;
    }

    if ($skip == 0) {   // Only try to enrol if they are not enrolled

        $moderatorRole = $DB->get_record('role', array('shortname' => $roleShortname));
        $enrol = enrol_get_plugin('manual');

        $community = $DB->get_record('course', array('id' => $courseId), '*', MUST_EXIST);
        $instances = enrol_get_instances($courseId, true);
        $enrolInstance = null;
        foreach ($instances as $dbinstance) {
            if ($dbinstance->enrol == 'manual') {
                $enrolInstance = $dbinstance;
                break;  // No need to look further
            }
        }

        if ($enrolInstance == null) {
            $instanceid = $enrol->add_default_instance($community);
            if ($instanceid === null) {
                $instanceid = $enrol->add_instance($community);
            }
            $enrolInstance = $DB->get_record('enrol', array('id' => $instanceid));
        }

        $enrol->enrol_user($enrolInstance, $userId, $moderatorRole->id);
    }
}

function remove_user_enrollment_from_course($courseId, $userId) {
    $context = context_course::instance($courseId);
    if (is_enrolled($context)) {    // Only try to remove enrollment if they are enrolled

        $enrol = enrol_get_plugin('manual');
        $instances = enrol_get_instances($courseId, true);
        $enrolInstance = null;
        foreach ($instances as $dbinstance) {
            if ($dbinstance->enrol == 'manual') {
                $enrolInstance = $dbinstance;
                break;  // No need to look further
            }
        }

        if ($enrolInstance != null) {   // There was no enrolment mechanism in course. No one was enrolled.
            $enrol->unenrol_user($enrolInstance, $userId);
        }
    }
}

/*!
 * \brief Search a key value pair within an array
 * \param $array, the array to search in
 * \param $key, the key to search for
 * \param $value, the value pair to search for
 * \return the found key value pair
 */
function search($array, $key, $value) {
    $results = array();
    search_r($array, $key, $value, $results);
    return $results;
}

/*!
 * \brief Assists the function search for recursive function
 * \param $array, the array to search in
 * \param $key, the key to search for
 * \param $value, the value pair to search for
 * \param $results, update this passed-by referrence result
 * \return breaks depth search
 */
function search_r($array, $key, $value, &$results) {
    if (!is_array($array)) {
        if (isset($array->$key) && $array->$key == $value) {
            $results[] = $array;
        }
        return;
    }

    foreach ($array as $subarray) {
        search_r($subarray, $key, $value, $results);
    }
}

function toggle_course_role($userid, $role, $courseid) {
    global $DB;

    $currentuserroles = get_user_roles(context_course::instance($courseid), $userid);
    foreach ($currentuserroles as $currentuserrole) {
        if (search($currentuserrole, 'roleid', $role)) {
            //already assigned
            return;
        }
        role_unassign_all(array('contextid' => context_course::instance($courseid)->id, 'userid' => $userid));
        role_assign($role, $userid, context_course::instance($courseid)->id);
    }
}

/*!
 * \brief get roleid from shortname
 * \param $shortname
 * \return string 
 */
function get_roleid_from_shortname($shortname) {
    global $DB;
    return $DB->get_field('role',  'id',  array('shortname' => $shortname));
    
}

?>