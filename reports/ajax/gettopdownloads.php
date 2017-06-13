<?php
require_once('../../../../config.php');
require_once('ajaxlib.php');
require_once($CFG->dirroot."/administration/lib.php");

$courseid = optional_param('courseid', '', PARAM_INT);
$unit = optional_param('unit', '', PARAM_INT);

global $USER, $DB;

if ($courseid == 1) {
    if (hasMasterAdminRole($USER->id)) {

    } else if (hasUnitAdminRole($USER->id)) {
        $courseidarr = array();
        //get all courses under unit
        $unitcourses = getCoursesFromUnit($unit);
        foreach ($unitcourses as $aunitcourse) {
            array_push($courseidarr, $aunitcourse->id);
        }
        if (sizeof($courseidarr) > 0 ) {
            $noOfCourses = sizeof($courseidarr);
            $courseid = implode(", ", $courseidarr);
        } else {
            echo json_encode(array()); exit;
        }
    } else {
        echo json_encode(array()); exit;
    }
}

$sdate = analytics_getStartDate();
$edate = analytics_getEndDate();
$topDl = getTopDownloads ($sdate, $edate, $courseid, 10);
foreach ($topDl as $key => $value) {
    $filecourseid = $value->custom_var_v1;
    $ccontextid = $DB->get_field('context', 'id', array('instanceid' => $filecourseid, 'contextlevel' => 50));
    $mfid = $DB->get_field('files', 'id', array('contextid' => $ccontextid, 'component' => 'course', 'filearea' => 'repository', 'filename' => $value->custom_var_v3));
    $temp = "<a href='".$CFG->wwwroot. "/blocks/course_filemanager/download_thumb.php?cid=".$filecourseid."&mfid=".$mfid."&contextid=".$ccontextid."&component=course&repo=course&d=/".$value->custom_var_v3."' title = 'Click to download ' style='font-weight: normal; color: #999999;'>".$value->custom_var_v3."</a>";
    $value->hreflink = $temp;
}
echo json_encode($topDl);

function getTopDownloads ($sdate, $edate = '', $courseid, $top = 50) {
    global $DB;

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }


    $dateqry = '';
    if ($startdate != '') {
        $dateqry .= " server_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " server_time <= '".$enddate."' ";
    }

    $courseflt = "";
    if ($courseid > 1) {
        $courseflt .= " and a.custom_var_v1 in (" . $courseid . ") ";
    }

    $sql = "
        SELECT newid(), c.id, c.fullname, custom_var_v3, custom_var_v1, cnt
        from (
            select custom_var_v3, custom_var_v1, count(*) as cnt
            from {analytic_log_link_visit_action} a
            where custom_var_v2 = 'Download' ".$courseflt." and ".$dateqry."
            group by custom_var_v3, custom_var_v1
        ) t,
          {course} c where c.id = t.custom_var_v1
        order by cnt desc";

    return $DB->get_records_sql($sql, array());
}


function analytics_getStartDate() {
    $usesession = true;
    $sdate = optional_param('start', 0, PARAM_RAW);
    if ($sdate != 0) {
        $startdate = new DateTime();
        $startdate->setTimestamp($sdate / 1000);
        $_SESSION['analytics_sdate'] = $startdate->getTimestamp();
    } else if (isset($_SESSION['analytics_sdate']) && $usesession) {
        $startdate = new DateTime();
        $startdate->setTimestamp($_SESSION['analytics_sdate']);
        
    } else {
        $startdate= new DateTime();
        $startdate->sub(new DateInterval("P7D"));
        $_SESSION['analytics_sdate'] = $startdate->getTimestamp();
    }
    return $startdate;
}

function analytics_getEndDate() {
    $usesession = true;
    $edate = optional_param('end', 0, PARAM_RAW);
    if ($edate != 0) {
        $enddate = new DateTime();
        $enddate->setTimestamp($edate / 1000);
        $_SESSION['analytics_edate'] = $enddate->getTimestamp();
    } else if (isset($_SESSION['analytics_edate']) && $usesession) {
        $enddate = new DateTime();
        $enddate->setTimestamp($_SESSION['analytics_edate']);
        $_SESSION['analytics_edate'] = $enddate->getTimestamp();
    } else {
        $enddate = new DateTime();
        $_SESSION['analytics_edate'] = $enddate->getTimestamp();
    }
    return $enddate;
}


?>