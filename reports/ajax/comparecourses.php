<?php
require_once('../../../../config.php');
require_once('ajaxlib.php');
require_once($CFG->dirroot."/administration/lib.php");

$courseid = optional_param('courseid', '', PARAM_INT);
$courseid2 = optional_param('courseid2', '', PARAM_INT);
$unit = optional_param('unit', '', PARAM_INT);

$sdate = analytics_getStartDate();
$edate = analytics_getEndDate();

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
            $courseid = implode(", ", $courseidarr);
        } else {
            echo json_encode(array()); exit;
        }
    } else {
        echo json_encode(array()); exit;
    }
}

$sql2 = "SELECT
            concat (
              case
                when cast (DATEPART(d, v.visit_first_action_time) as int)  < 10
                then concat ('0', DATEPART(d, v.visit_first_action_time))
                else concat (DATEPART(d, v.visit_first_action_time), '')
              end,
              '-',
              case
                when cast (DATEPART(MM, v.visit_first_action_time) as int)  < 10
                then concat ('0', DATEPART(MM, v.visit_first_action_time))
                else concat (DATEPART(MM, v.visit_first_action_time), '')
              end,
              '-',
              DATEPART(yy, v.visit_first_action_time)
            ) as date,        
            count(case when va.custom_var_v1 in (".$courseid.") then 1 else null end) as totalvisit1,
            count(case va.custom_var_v1 when ".$courseid2." then 1 else null end) as totalvisit2
            from {analytic_log_visit} v 
            join (select distinct idvisit, custom_var_v1 from {analytic_log_link_visit_action} where custom_var_v1 in (".$courseid.",".$courseid2.")) va 
            on v.idvisit = va.idvisit
            group by
              concat (
                case
                  when cast (DATEPART(d, v.visit_first_action_time) as int)  < 10
                  then concat ('0', DATEPART(d, v.visit_first_action_time))
                  else concat (DATEPART(d, v.visit_first_action_time), '')
                end,
                '-',
                case
                  when cast (DATEPART(MM, v.visit_first_action_time) as int)  < 10
                  then concat ('0', DATEPART(MM, v.visit_first_action_time))
                  else concat (DATEPART(MM, v.visit_first_action_time), '')
                end,
                '-',
                DATEPART(yy, v.visit_first_action_time)
              )";

        $recs = $DB->get_records_sql($sql2);
        $tempitem = new stdClass();
        $tempitem ->date = '';
        $tempitem->totalvisit1 = 0;
        $tempitem->totalvisit2 = 0;

        $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
        //$report->graphdata = json_encode($ary);


echo json_encode($ary);

function getDateArray ($sdate = '' , $edate = '') {
    if ($sdate == '') {
        $sdate = new DateTime();
        $sdate->sub(new DateInterval("P30D"));
    }
    if ($edate == '') {
        $edate = new DateTime();
    }

    $daterange = new DatePeriod($sdate, new DateInterval(("P1D")) ,$edate);
    $ary = array();
    foreach($daterange as $date){
        $ary[] = $date->format("d-m-Y");
    }
    return $ary;
}

function getDateRangeData ($recs, $sdate, $edate, $tempitem, $field) {
    $ary = array();
    $ary2 = getDateArray($sdate, $edate);
    foreach ($ary2 as $dte) {
        $found = false;
        foreach ($recs as $rec) {
            if ($rec->$field == $dte) {
                $ary[] = $rec;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $newitem = clone($tempitem);
            $newitem->$field = $dte;
            $ary[] = $newitem;
        }
    }
    return $ary;
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