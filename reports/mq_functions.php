<?php

function pregmatch_function($data, $charsize=30, $url = "") {
	$str = preg_replace('/\s+?(\S+)?$\./', '', 
	substr(strip_tags(stripslashes($data)), 0, $charsize));
	
	if(strlen($data) > $charsize)
	{
		$str .= '...';
        if ($url != "") {
            $str .= '<a href="' . $url . '">View More</a>';
        }
	}
	return $str;
	/* 
	$pattern = '/^([^.!?\s]*[\.!?\s]+){0,'.$charsize.'}/';
	preg_match($pattern, strip_tags(stripslashes($data)), $abstract);
	return $abstract[0]; 
	*/
}

function GetTimeAgo($time) {
    $currentTime = date('Y-m-d H:i:s');
    $toTime = strtotime($currentTime);
 
    $fromTime = strtotime(date('Y-m-d H:i:s', $time));
 
    $timeDiff = floor(abs($toTime - $fromTime) / 60);
 
    if ($timeDiff < 2) {
        $timeDiff = "Just now";
    } elseif ($timeDiff > 2 && $timeDiff < 60) {
        $timeDiff = floor(abs($timeDiff)) . " minutes ago";
    } elseif ($timeDiff > 60 && $timeDiff < 120) {
        $timeDiff = floor(abs($timeDiff / 60)) . " hour ago";
    } elseif ($timeDiff < 1440) {
        $timeDiff = floor(abs($timeDiff / 60)) . " hours ago";
    } elseif ($timeDiff > 1440 && $timeDiff < 2880) {
        $timeDiff = floor(abs($timeDiff / 1440)) . " day ago";
    } elseif ($timeDiff > 2880) {
        $timeDiff = floor(abs($timeDiff / 1440)) . " days ago";
    }
 
    return $timeDiff;
}

function checkIfOwnerOfFile($id) {
    global $DB, $USER;
    // Get userid from file repository
    $sql = "SELECT userid FROM {files} where id = " . $id;
    $isOwner = $DB->get_records_sql($sql);
    $isOwner = array_pop($isOwner);
    if ($isOwner->userid == $USER->id) {
        return true;
    } else {
        return false;
    }
}

function checkIfInstructorOfCourse($courseid) {
    global $DB, $USER;
    // Get Role id for instructors
    $rolenames=$DB->get_records_sql("select id from {role} where shortname in ('manager','coursecreator','editingteacher','teacher','communitymanager')");
    $roleids = '';
    if (!empty($rolenames)) {
        $roleids = implode(",", array_keys($rolenames));
    }
    $sql = 'select id from {user} where id in (select userid from {role_assignments} where contextid in (select id from {context} where contextlevel = 50 and instanceid= '
        . $courseid . ') and roleid in (' . $roleids . '))';
    $instructors = $DB->get_fieldset_sql($sql);
    if (in_array($USER->id, $instructors)) {
        return true;
    } else {
        return false;
    }
}

function checkIfUnitAdminOfCourse($courseid) {
    global $DB, $USER;
    // Get Role id for instructors
    $category = $DB->get_field("course", "category", array("id" => $courseid));
    $sql = 'SELECT userid from {role_assignments} where contextid in (select id from {context} where instanceid= '
           . $category . ') and roleid = 9';
    $unitadmins = $DB->get_fieldset_sql($sql);
    if (in_array($USER->id, $unitadmins)) {
        return true;
    } else {
        return false;
    }
}

function checkInCommunityPermission($courseid) {
    global $DB;
    $enroltype = $DB->get_field('course_options2', 'enrollmenttype', array('courseid' => $courseid));
    $isPublicClose = strcasecmp($enroltype, 'Public') == 0;
    if ($isPublicClose === true || strcasecmp($enroltype, 'Closed') == 0) {
    // Only check if is public(closed) or private
        $context = context_course::instance($courseid);
        if (is_enrolled($context)) {
            return 1;
        } else if ($isPublicClose == true) {   // Return 0 if is public close
            return 0;
        } else {    // Return -1 if is private
            return -1;
        }
    }
    return 2;   // Default take as public open
}

function checkIfSelfEnrolled($courseid) {
    global $DB, $USER;
    $sql1 = "SELECT userid FROM {user_enrolments} WHERE enrolid IN (SELECT id FROM {enrol} WHERE courseid = ? and enrol <> 'self' )";
    $members1 = $DB->get_fieldset_sql($sql1, array($courseid));
    if (in_array($USER->id, $members1)) {
        return false;
    } else {
        return true;
    }
}

function checkAccessForCourse ($courseid) {
    global $DB, $USER;
    if ($USER->id == 2)
        return;
    if ($courseid < 1)
        return;
    $coursetype = $DB->get_field('course_options2', 'coursetype', array('courseid' => $courseid));
    $context = context_course::instance($courseid);
    if (strcasecmp($coursetype, 'meta') == 0) {
        return true;
    } else if (strcasecmp($coursetype, 'child') == 0) {
        if (!is_enrolled($context)) {
            redirect($CFG->wwwroot);
        } else {
            return true;
        }
    } else { //left community or event
        $commPermission = checkInCommunityPermission($courseid);
        if ($commPermission <=0) {                
            redirect($CFG->wwwroot);
        } else {
            return true;
        }
    }
}
?>
