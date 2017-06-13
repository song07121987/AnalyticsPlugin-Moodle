<?php
require_once('../../../../config.php');
require_once('ajaxlib.php');
require_once($CFG->dirroot."/administration/lib.php");

$courseid = optional_param('courseid', '', PARAM_INT);
$unit = optional_param('unit', '', PARAM_INT);

global $USER;

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
echo json_encode(getPageReport ($sdate, $edate, $courseid, 10));

function getPageReport ($sdate, $edate = '', $courseid, $top = 50) {
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
    SELECT top ".$top." * from (
        select distinct(idaction), name, visitcount, uniqueview, generationtime, timespent, bouncepages, latest_server_time
        from
            (select a.idaction_name,
                count(*) as visitcount,
                count(distinct(v.idvisit)) as uniqueview,
                avg(custom_float) / 1000 as generationtime,
                avg (time_spent_ref_action)  as timespent,
                sum (cast (case when v.visit_exit_idaction_name = a.idaction_name then 1  else 0 end as int)) as bouncepages,
                max(server_time) as latest_server_time
            from {analytic_log_link_visit_action} a,
                {analytic_log_visit} v
            where ".$dateqry."
                ".$courseflt."
                and a.idvisit = v.idvisit
            group by idaction_name) t,
            {analytic_log_action} c
        where t.idaction_name = c.idaction )t2
    order by latest_server_time desc";

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