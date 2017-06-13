<?php
require_once ('../../../config.php');

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
        // echo "from session";
        // print_r ($startdate);
    } else {
        $startdate= new DateTime();
        $startdate->sub(new DateInterval("P7D"));
        $_SESSION['analytics_sdate'] = $startdate->getTimestamp();
    }
    return $startdate;
}

function analytics_getStartDate30() {
    $usesession = true;
    $sdate = optional_param('start', 0, PARAM_RAW);
    if ($sdate != 0) {
        $startdate = new DateTime();
        $startdate->setTimestamp($sdate / 1000);
        $_SESSION['analytics_sdate'] = $startdate->getTimestamp();
    } else if (isset($_SESSION['analytics_sdate']) && $usesession) {
        $startdate = new DateTime();
        $startdate->setTimestamp($_SESSION['analytics_sdate']);
        // echo "from session";
        // print_r ($startdate);
    } else {
        $startdate= new DateTime();
        $startdate->sub(new DateInterval("P30D"));
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
        // echo "from session";
        // print_r ($enddate);
    } else {
        $enddate = new DateTime();
        $_SESSION['analytics_edate'] = $enddate->getTimestamp();
    }
    return $enddate;
}


function getReport ($reportname, $cid, $startdate) {
    $cache = cache::make('local_analytics', 'reportitems');
    $startdate2 = str_replace ('-', '', $startdate);
    return $cache->get($reportname.$cid.$startdate2);
}

function setReport ($reportname, $cid, $startdate, $json) {
    $cache = cache::make('local_analytics', 'reportitems');
    $startdate2 = str_replace ('-', '', $startdate);
    $cache->set($reportname.$cid.$startdate2, $json);
}

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

function getLandingReport ($sdate = '', $edate = '', $courseid = 0, $courseid2 = 0) {
    global $DB, $USER;
    $rptname = 'RptLanding';
    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '')
        $startdate = $sdate->format("Y-m-d");
    if ($edate != '') {
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");

    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }
    $pastweek = strtotime("-1 week");
    $report->topdashboard = getDashboardCounts($USER->id, $pastweek);

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " visit_first_action_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }

    $where = " where ".$dateqry. " ";    

    if ($courseid > 1) {
        $where .= " and idvisit in(select distinct(idvisit) from mdl_analytic_log_link_visit_action where custom_var_v1 = ".$courseid.") ";
    }

   // Global Stats
    $sql = "
        select
            count(*) as totalvisit,
            count(distinct(user_id)) as uniquevisitor,
            sum(visitor_returning) as totalrepeatvisit,
            sum(visit_total_actions) as totalactions,
            max(visit_total_actions) as maxactions,
            sum(visit_total_time) as totaltime,
            max(visit_total_time / visit_total_actions) as maxtime,
            sum(visit_total_searches) as totalsearches,
            max(visit_total_searches) as maxsearches,
            sum (cast (case when custom_var_v1 ='Web' then 1 else 0  end as int)) as totalwebvisit,
            sum (cast (case when custom_var_v1 ='Mobile' then 1 else 0  end as int)) as totalmobilevisit,
            sum (cast (case when custom_var_v1 ='Web' then visit_total_actions else 0  end as int)) as totalwebactions,
            sum (cast (case when custom_var_v1 ='Mobile' then visit_total_actions else 0  end as int)) as totalmobileactions,
            sum (cast (case when custom_var_v1 ='Web' then visit_total_time else 0  end as int)) as totalwebtime,
            sum (cast (case when custom_var_v1 ='Mobile' then visit_total_time else 0  end as int)) as totalmobiletime
        from {analytic_log_visit} ".$where;

    $bind = array();
    $rec = $DB->get_record_sql($sql, $bind);
    $report->stats = $rec;

    $sql = "
      select
        concat (
          case
            when cast (DATEPART(d, visit_first_action_time) as int)  < 10
            then concat ('0', DATEPART(d, visit_first_action_time))
            else concat (DATEPART(d, visit_first_action_time), '')
          end,
          '-',
          case
            when cast (DATEPART(MM, visit_first_action_time) as int)  < 10
            then concat ('0', DATEPART(MM, visit_first_action_time))
            else concat (DATEPART(MM, visit_first_action_time), '')
          end,
          '-',
          DATEPART(yy, visit_first_action_time)
        ) as date,
        count(*) as totalvisit1,
        count (distinct idvisitor) as uniquevisit1,
        sum(visit_total_actions) as totalaction,
        sum(visit_total_time) as totaltime,
        sum(visitor_returning) as totalreturning,
        sum(visit_total_time) / sum(visit_total_actions) as timeperaction
        from mdl_analytic_log_visit ".$where."
        group by
          concat (
            case
              when cast (DATEPART(d, visit_first_action_time) as int)  < 10
              then concat ('0', DATEPART(d, visit_first_action_time))
              else concat (DATEPART(d, visit_first_action_time), '')
            end,
            '-',
            case
              when cast (DATEPART(MM, visit_first_action_time) as int)  < 10
              then concat ('0', DATEPART(MM, visit_first_action_time))
              else concat (DATEPART(MM, visit_first_action_time), '')
            end,
            '-',
            DATEPART(yy, visit_first_action_time)
          ) ";

    $recs = $DB->get_records_sql($sql, $bind);
    $tempitem = new stdClass();
    $tempitem ->date = '';
    $tempitem->totalvisit1 = 0;
    $tempitem->uniquevisit1 = 0;
    $tempitem->totalaction = 0;
    $tempitem->totaltime = 0;
    $tempitem->totalreturning = 0;
    $tempitem->timeperaction = 0;

    $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');

    $report->graphdata = json_encode($ary);

    if ($courseid2 > 0) {

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
            count(case va.custom_var_v1 when ".$courseid." then 1 else null end) as totalvisit1,
            count(case va.custom_var_v1 when ".$courseid2." then 1 else null end) as totalvisit2
            from mdl_analytic_log_visit v 
            join (select distinct idvisit, custom_var_v1 from mdl_analytic_log_link_visit_action where custom_var_v1 in (".$courseid.",".$courseid2.")) va 
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

        $recs = $DB->get_records_sql($sql2, $bind);
        $tempitem = new stdClass();
        $tempitem ->date = '';
        $tempitem->totalvisit1 = 0;
        $tempitem->totalvisit2 = 0;

        $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
        $report->graphdata = json_encode($ary);
    }

    $contextid = 0;
    if ($courseid > 1) {
        $sql = "select id from {context} where contextlevel = 50 and instanceid = ".$courseid;
        $rec = $DB->get_record_sql ($sql, array());
        $contextid = $rec->id;
    }

    $sql = "
        select m.name,
               count(*) as nummodules,
               count(distinct(c.course)) as csecount,
                sum (cast (case when c.course = ".$courseid." then 1 else 0  end as int)) as csemodcount
        from mdl_course_modules c, mdl_modules m where c.module = m.id
        group by m.name
        UNION
        select 'forumposts' as name, count(*) as nummodules, count(distinct (d.course)) as csecount,
        sum (cast (case when d.course = ".$courseid." then 1 else 0 end as int)) as csemodcount
        from mdl_forum_posts p, mdl_forum_discussions d
        where p.discussion = d.id
        UNION
        select 'files' as name, count(*) as nummodules, count(distinct (contextid)) as csecount,
        sum (cast (case when contextid = ".$contextid." then 1 else 0 end as int)) as csemodcount
        from mdl_files
        where component = 'course' and filearea = 'repository'";

    $tbldata = $DB->get_records_sql($sql, $bind);
    $report->tbldata = $tbldata;

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " a.server_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry = " a.server_time <= '".$enddate."' ";
    }

    $where = " where ".$dateqry. " ";
    if ($courseid > 1) {
        $where .= " and a.custom_var_v1 = ".$courseid." ";
    }


    $sql = "
            select * from
                (select top 5 newid() as id, a.idaction_name, c.name, v.custom_var_v1, count(*) as cnt
                from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v, mdl_analytic_log_action c
                ".$where."
                    and a.idvisit = v.idvisit
                    and a.idaction_name = c.idaction
                    and v.custom_var_v1 = 'Web'
                group by a.idaction_name, c.name, v.custom_var_v1  order by cnt desc) t
            UNION
            select top 5 newid() as id, a.idaction_name, c.name, v.custom_var_v1, count(*) as cnt
            from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v, mdl_analytic_log_action c
                ".$where."
                and a.idvisit = v.idvisit
                and a.idaction_name = c.idaction
                and v.custom_var_v1 = 'Mobile'
            group by a.idaction_name, c.name, v.custom_var_v1  order by cnt desc";

    $topdata = $DB->get_records_sql($sql, $bind);
    $report->toppages = $topdata;


    getPageReport ($sdate, $edate, $courseid, 10 , $report);
    getDownloadReport ($sdate, $edate, $courseid, $report);

    getCourseForumReport ($sdate, $edate, $courseid, 10 , $report);
    
    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;
}

function getLandingReport2 ($sdate = '', $edate = '', $courseid = 0) {
    global $DB;
    $rptname = 'RptLanding2';
    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '')
        $startdate = $sdate->format("Y-m-d");
    if ($edate != '') {
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");

    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " visit_first_action_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }

    $where = " where ".$dateqry. " ";

    if ($courseid > 1) {
        $where .= " and idvisit in(select distinct(idvisit) from mdl_analytic_log_link_visit_action where custom_var_v1 = ".$courseid.") ";
    }

   // Global Stats
    $sql = "
        select
            count(*) as totalvisit,
	        count(distinct(user_id)) as uniquevisitor,
            sum(visitor_returning) as totalrepeatvisit,
            sum(visit_total_actions) as totalactions,
            max(visit_total_actions) as maxactions,
            sum(visit_total_time) as totaltime,
            max(visit_total_time / visit_total_actions) as maxtime,
            sum(visit_total_searches) as totalsearches,
            max(visit_total_searches) as maxsearches,
            sum (cast (case when custom_var_v1 ='Web' then 1 else 0  end as int)) as totalwebvisit,
            sum (cast (case when custom_var_v1 ='Mobile' then 1 else 0  end as int)) as totalmobilevisit,
            sum (cast (case when custom_var_v1 ='Web' then visit_total_actions else 0  end as int)) as totalwebactions,
            sum (cast (case when custom_var_v1 ='Mobile' then visit_total_actions else 0  end as int)) as totalmobileactions,
            sum (cast (case when custom_var_v1 ='Web' then visit_total_time else 0  end as int)) as totalwebtime,
            sum (cast (case when custom_var_v1 ='Mobile' then visit_total_time else 0  end as int)) as totalmobiletime
        from {analytic_log_visit} ".$where;

    $bind = array();
    $rec = $DB->get_record_sql($sql, $bind);
    $report->stats = $rec;

    $sql = "
      select
	    concat (
		  case
			when cast (DATEPART(d, visit_first_action_time) as int)  < 10
			then concat ('0', DATEPART(d, visit_first_action_time))
			else concat (DATEPART(d, visit_first_action_time), '')
		  end,
		  '-',
		  case
			when cast (DATEPART(MM, visit_first_action_time) as int)  < 10
			then concat ('0', DATEPART(MM, visit_first_action_time))
			else concat (DATEPART(MM, visit_first_action_time), '')
		  end,
		  '-',
		  DATEPART(yy, visit_first_action_time)
	    ) as date,
		count(*) as totalvisit,
		sum(visit_total_actions) as totalaction,
		sum(visit_total_time) as totaltime,
		sum(visitor_returning) as totalreturning,
		sum(visit_total_time) / sum(visit_total_actions) as timeperaction
	    from mdl_analytic_log_visit ".$where."
        group by
	      concat (
		    case
			  when cast (DATEPART(d, visit_first_action_time) as int)  < 10
			  then concat ('0', DATEPART(d, visit_first_action_time))
			  else concat (DATEPART(d, visit_first_action_time), '')
		    end,
		    '-',
		    case
			  when cast (DATEPART(MM, visit_first_action_time) as int)  < 10
			  then concat ('0', DATEPART(MM, visit_first_action_time))
			  else concat (DATEPART(MM, visit_first_action_time), '')
		    end,
		    '-',
		    DATEPART(yy, visit_first_action_time)
	      ) ";

    $recs = $DB->get_records_sql($sql, $bind);
    $tempitem = new stdClass();
    $tempitem ->date = '';
    $tempitem->totalvisit = 0;
    $tempitem->totalaction = 0;
    $tempitem->totaltime = 0;
    $tempitem->totalreturning = 0;
    $tempitem->timeperaction = 0;

    $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');

    $report->graphdata = json_encode($ary);

    $contextid = 0;
    if ($courseid > 1) {
        $sql = "select id from {context} where contextlevel = 50 and instanceid = ".$courseid;
        $rec = $DB->get_record_sql ($sql, array());
        $contextid = $rec->id;
    }

    $sql = "
        select m.name,
               count(*) as nummodules,
               count(distinct(c.course)) as csecount,
                sum (cast (case when c.course = ".$courseid." then 1 else 0  end as int)) as csemodcount
        from mdl_course_modules c, mdl_modules m where c.module = m.id
        group by m.name
		UNION
		select 'forumposts' as name, count(*) as nummodules, count(distinct (d.course)) as csecount,
		sum (cast (case when d.course = ".$courseid." then 1 else 0 end as int)) as csemodcount
		from mdl_forum_posts p, mdl_forum_discussions d
		where p.discussion = d.id
		UNION
		select 'files' as name, count(*) as nummodules, count(distinct (contextid)) as csecount,
		sum (cast (case when contextid = ".$contextid." then 1 else 0 end as int)) as csemodcount
		from mdl_files
		where component = 'course' and filearea = 'repository'";

    $tbldata = $DB->get_records_sql($sql, $bind);
    $report->tbldata = $tbldata;

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " a.server_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry = " a.server_time <= '".$enddate."' ";
    }

    $where = " where ".$dateqry. " ";
    if ($courseid > 1) {
        $where .= " and a.custom_var_v1 = ".$courseid." ";
    }


    $sql = "
            select * from
                (select top 5 newid() as id, a.idaction_name, c.name, v.custom_var_v1, count(*) as cnt
                from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v, mdl_analytic_log_action c
                ".$where."
                    and a.idvisit = v.idvisit
                    and a.idaction_name = c.idaction
                    and v.custom_var_v1 = 'Web'
                group by a.idaction_name, c.name, v.custom_var_v1  order by cnt desc) t
            UNION
            select top 5 newid() as id, a.idaction_name, c.name, v.custom_var_v1, count(*) as cnt
            from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v, mdl_analytic_log_action c
                ".$where."
                and a.idvisit = v.idvisit
                and a.idaction_name = c.idaction
                and v.custom_var_v1 = 'Mobile'
            group by a.idaction_name, c.name, v.custom_var_v1  order by cnt desc";

    $topdata = $DB->get_records_sql($sql, $bind);
    $report->toppages = $topdata;

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;
}


function getCourseBreakdownReport ($sdate, $edate = '') {
    global $DB, $USER;
    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '')
        $startdate = $sdate->format("Y-m-d");
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $rptname = 'RptCourseBreak';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, 1, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " server_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " server_time <= '".$enddate."' ";
    }
    $unitadminsql = '';
    if (!hasMasterAdminRole($USER->id) ) {
        if (hasUnitAdminRole($USER->id)) {
            $unitadminsql = "and c.category in (SELECT e.id
                FROM {course_categories} e
                JOIN {course_categories_ext} f  on   e.id = f.categoryid
                JOIN {context} c on f.categoryid = c.instanceid
                JOIN {role_assignments} ra on ra.contextid = c.id
                JOIN {role} r on r.id = ra.roleid
                WHERE contextlevel= 40 and deleted='0' and shortname = 'unitadmin'  and userid = $USER->id)";            
        }
    }

    $sql = "
        select
            c.category,
            courseid,
            c.fullname,
            count(idvisit) as totalvisits,
            count ((cast  ( case when custom_var_v1 = 'Web' then  idvisit else null end as int))) as totalwebvisit,
            count ((cast  ( case when custom_var_v1 = 'Mobile' then  idvisit else null end as int))) as totalmobilevisit,
            sum (visit_total_actions) as totalaction,
            sum (cast  ( case when custom_var_v1 = 'Web' then  visit_total_actions else 0 end as int)) as totalwebactions,
            sum (cast  ( case when custom_var_v1 = 'Mobile' then  visit_total_actions else 0 end as int)) as totalmobileactions,
            count (distinct(user_id)) as totaluniqueuser,
            count (distinct (cast  ( case when custom_var_v1 = 'Web' then  user_id else null end as int))) as uniquewebuser,
            count (distinct (cast  ( case when custom_var_v1 = 'Mobile' then  user_id else null end as int))) as uniquemobileuser,
            avg (visit_total_actions) as avgactions,
            avg (cast  ( case when custom_var_v1 = 'Web' then  visit_total_actions else 0 end as int)) as avgwebactions,
            avg (cast  ( case when custom_var_v1 = 'Mobile' then  visit_total_actions else 0 end as int)) as avgmobileactions
        from (
            select a.custom_var_v1 as courseid, a.idvisit as idvisit, v.custom_var_v1, v.user_id, count(*) as visit_total_actions
            from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v
            where ".$dateqry." and a.idvisit = v.idvisit and
              a.custom_var_v1 != 1
            group by a.custom_var_v1, a.idvisit, v.custom_var_v1, v.user_id
        ) t, mdl_course c 
        where c.id = courseid ".$unitadminsql."
        group by courseid, c.fullname, c.category";

        

    $report->courses = $DB->get_records_sql($sql, array());

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " visit_first_action_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }

    $where = " where ".$dateqry. " ";
    $sql = "
        select
            count(*) as totalvisit,
            count(distinct(user_id)) as uniquevisitor,
            sum(visitor_returning) as totalrepeatvisit,
            sum(visit_total_actions) as totalactions,
            max(visit_total_actions) as maxactions,
            sum(visit_total_time) as totaltime,
            max(visit_total_time / visit_total_actions) as maxtime,
            sum(visit_total_searches) as totalsearches,
            max(visit_total_searches) as maxsearches,
            sum (cast (case when custom_var_v1 ='Web' then 1 else 0  end as int)) as totalwebvisit,
            sum (cast (case when custom_var_v1 ='Mobile' then 1 else 0  end as int)) as totalmobilevisit,
            sum (cast (case when custom_var_v1 ='Web' then visit_total_actions else 0  end as int)) as totalwebactions,
            sum (cast (case when custom_var_v1 ='Mobile' then visit_total_actions else 0  end as int)) as totalmobileactions,
            sum (cast (case when custom_var_v1 ='Web' then visit_total_time else 0  end as int)) as totalwebtime,
            sum (cast (case when custom_var_v1 ='Mobile' then visit_total_time else 0  end as int)) as totalmobiletime
        from {analytic_log_visit} ".$where;

    $bind = array();
    $rec = $DB->get_record_sql($sql, $bind);
    $report->stats = $rec;

    setReport($rptname, 1, $startdate.$enddate, json_encode($report));
    return $report;
}

function getUserSummaryReport ($sdate, $edate = '', $courseid)
{
    global $DB;
    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '')
        $startdate = $sdate->format("Y-m-d");
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $rptname = 'RptUserSummary';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " server_time >= '" . $startdate . "' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " server_time <= '" . $enddate . "' ";
    }

    $courseflt = "";
    if ($courseid > 1) {
        $courseflt .= " and a.custom_var_v1 = " . $courseid . " ";
    }

    $sql = "
        select u.id, u.firstname, count(*) as totalvisit, sum(time_spent) as totaltime, sum(visit_total_actions) as totalactions
        from (
            select
              a.custom_var_v1 as courseid, a.idvisit as idvisit, v.custom_var_v1, v.user_id,
              count(*) as visit_total_actions,
              sum(time_spent_ref_action) as time_spent
            from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v
            where ".$dateqry." and a.idvisit = v.idvisit
              " . $courseflt . "
            group by a.custom_var_v1, a.idvisit, v.custom_var_v1, v.user_id) t, mdl_user u
        where user_id = u.id
        group by u.id, u.firstname order by u.id";

    $recs  = $DB->get_records_sql($sql, array());
    $report->tbldata = $recs;

    $sql = "
        select max(numusers) as numusers, max(loggedin) as loggedin, max(loggedinperiod) as loggedinperiod, max(totalaction) as totalaction, max(totaltime) as totaltime
        from (
            select count(*) as numusers,
                count (cast (case when firstaccess > 0 then 1 else 0 end as int)) as loggedin, 0 as loggedinperiod, 0 as totalaction, 0 as totaltime
            from mdl_user where deleted = 0
            UNION
            select 0, 0, count(distinct(user_id)), count (*), sum(time_spent_ref_action)
            from mdl_analytic_log_link_visit_action a, mdl_analytic_log_visit v
            where ".$dateqry." and a.idvisit = v.idvisit
        ) t ";

    $stats = $DB->get_record_sql($sql, array());
    $report->stats = $stats;

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;
}

function getTopContributors ($sdate, $edate = '', $courseid) {
    global $DB, $CFG;
    $report = new stdClass();
	
	$showenhancedanalytics = get_config('local_analytics', 'showenhancedanalytics');
	
    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        // $startdate = $sdate->format("Y-m-d");
        $starttime = $sdate->getTimestamp();
    }
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        // $enddate = $edate2->format("Y-m-d");
        $endtime = $edate2->getTimestamp();
    }

    $rptname = 'RptContrib';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($starttime != '') {
        $dateqry = " created >= '" . $starttime . "' ";
    }
    if ($endtime != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " created <= '" . $endtime . "' ";
    }

    $courseflt = "";
    if ($courseid > 1) {
        $courseflt = " and d.course  = " . $courseid . " ";
    }
    $sql = "
        select TOP 50 p.userid, u.firstname,
          count(*) as cnt,
          sum (cast (case when ".$dateqry." then 1 else 0 end as int)) as periodcnt
        from mdl_forum_posts p, mdl_forum_discussions d, mdl_user u
        where p.discussion = d.id
            and p.userid = u.id
            ".$courseflt."
        group by p.userid, u.firstname
        order by cnt desc, periodcnt desc";

    $forum = $DB->get_records_sql($sql, array());
    $report->forums = $forum;

    if ($showenhancedanalytics) {
        if ($courseid > 1) {
            $courseflt = " and d.courseid  = " . $courseid . " ";
        }

        $sql = "
          select TOP 50 d.userid, u.firstname,
            count(*) as cnt,
              sum (cast (case when " . $dateqry . " then 1 else 0 end as int)) as periodcnt
          from mdl_post d, mdl_user u
          where d.userid = u.id
              " . $courseflt . "
          group by d.userid, u.firstname
            order by cnt desc, periodcnt desc";

        $blogs = $DB->get_records_sql($sql, array());
        $report->blogs = $blogs;
        $contextflt = "";
        if ($courseid > 1) {
            $sql = "select id from {context} where contextlevel = 50 and instanceid = " . $courseid;
            $rec = $DB->get_record_sql($sql, array());
            $contextflt = " and contextid = " . $rec->id . " ";
        }

        $sql = "
          select u.id, u.firstname, cnt
          from (
              select top 50 f.userid,
                count(*) as cnt
              from mdl_files f
              where
                ((component = 'course' and filearea = 'repository')
                or (component = 'user' and filearea = 'private'))
                " . $contextflt . "
              group by f.userid
              order by cnt desc) t,
            mdl_user u
          where t.userid = u.id
          order by cnt desc";

        $files = $DB->get_records_sql($sql, array());
        $report->files = $files;
    }

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;
}

function getVisitorMap ($sdate, $edate = '', $courseid) {
    global $DB;
    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $rptname = 'RptVisitorMap';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry .= " visit_first_action_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }

    $courseflt = "";
    if ($courseid > 1) {
        $courseflt .= " and d.course  = " . $courseid . " ";
    }

    /*
    $sql = "
        select location_country,
            count(*) as visits,
            count(distinct (user_id)) as users,
            sum(visit_total_actions) as actions,
            sum(visit_total_searches) as searches,
            sum(visit_total_time) as totaltime,
            sum (cast (case when ".$dateqry." then 1 else 0 end as int)) as visitsperiod,
            sum(distinct (cast (case when ".$dateqry." then user_id else null end as int))) as usersperiod,
            sum(cast (case when ".$dateqry." then visit_total_actions else 0 end as int)) as actionsperiod,
            sum(cast (case when ".$dateqry." then visit_total_searches else 0 end as int)) as searchesperiod,
            sum(cast (case when ".$dateqry." then visit_total_time else 0 end as int)) as timeperiod
        from mdl_analytic_log_visit
        group by location_country";
    */
    $sql = "
        select location_country,
            sum (cast (case when ".$dateqry." then 1 else 0 end as int)) as visitsperiod,
            count(distinct (cast (case when ".$dateqry." then user_id else null end as int))) as usersperiod,
            sum(cast (case when ".$dateqry." then visit_total_actions else 0 end as int)) as actionsperiod,
            sum(cast (case when ".$dateqry." then visit_total_time else 0 end as int)) as timeperiod
        from mdl_analytic_log_visit
        group by location_country";

    $countries = $DB->get_records_sql($sql, array());
    $report->countries = $countries;

    $data = "";
    $idx = 0;
    $unknown = 0;
    $visits = 0;
    $actions = 0;
    $users = 0;
    foreach ($countries as $country) {
        if ($country->visitsperiod > 0) {
            $latlong = getLatLong($country->location_country);
            if ($latlong != '') {
                if ($idx > 0)
                    $data .= ", ";
                $data .= " {latLng: [" . $latlong . "], name : '" . getCountry($country->location_country) . " (" . $country->visitsperiod . " visits)'}";
                $idx++;
            } else {
                $unknown += $country->visits;
            }
            $visits += $country->visitsperiod;
            $actions += $country->actionsperiod;
            $users += $country->usersperiod;
        }
    }

    if ($unknown > 0) {
        if ($idx > 0)
            $data .= ", ";
        $data .= " {latLng: [".$latlong."], name : '". getCountry(""). " (". $unknown ." visits)'}";
    }

    $report->countrydata = $data;
    $report->totalvisits = $visits;
    $report->totalactions = $actions;
    $report->totalusers = $users;

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;

    /*
            {latLng: [41.90, 12.45], name: 'Vatican City'},
            {latLng: [43.73, 7.41], name: 'Monaco'},
            {latLng: [-0.52, 166.93], name: 'Nauru'},
            {latLng: [-8.51, 179.21], name: 'Tuvalu'},
            {latLng: [43.93, 12.46], name: 'San Marino'},
            {latLng: [47.14, 9.52], name: 'Liechtenstein'},
            {latLng: [7.11, 171.06], name: 'Marshall Islands'},
            {latLng: [17.3, -62.73], name: 'Saint Kitts and Nevis'},
            {latLng: [3.2, 73.22], name: 'Maldives'},
            {latLng: [35.88, 14.5], name: 'Malta'},
            {latLng: [12.05, -61.75], name: 'Grenada'},
            {latLng: [13.16, -61.23], name: 'Saint Vincent and the Grenadines'},
            {latLng: [13.16, -59.55], name: 'Barbados'},
            {latLng: [17.11, -61.85], name: 'Antigua and Barbuda'},
            {latLng: [-4.61, 55.45], name: 'Seychelles'},
            {latLng: [7.35, 134.46], name: 'Palau'},
            {latLng: [42.5, 1.51], name: 'Andorra'},
            {latLng: [14.01, -60.98], name: 'Saint Lucia'},
            {latLng: [6.91, 158.18], name: 'Federated States of Micronesia'},
            {latLng: [1.3, 103.8], name: 'Singapore - 5 Visits'},
            {latLng: [1.46, 173.03], name: 'Kiribati'},
            {latLng: [-21.13, -175.2], name: 'Tonga'},
            {latLng: [15.3, -61.38], name: 'Dominica'},
            {latLng: [-20.2, 57.5], name: 'Mauritius'},
            {latLng: [26.02, 50.55], name: 'Bahrain'},
            {latLng: [0.33, 6.73], name: 'São Tomé and Príncipe'}
     */

}

function getPageReport ($sdate, $edate = '', $courseid, $top = 50 , &$report = null) {
    global $DB;
    $dashboard = false;
    if ($report == null) {
        $report = new stdClass();    
    } else {
        $dashboard = true;
    }

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $rptname = 'RptPage';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr && !$dashboard) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
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
        $courseflt .= " and a.custom_var_v1 = " . $courseid . " ";
    }

    $sql = "
    Select top ".$top." * from (
        select distinct(idaction), name, visitcount, uniqueview, generationtime, timespent, bouncepages, latest_server_time
        from
            (select a.idaction_name,
                count(*) as visitcount,
                count(distinct(v.idvisit)) as uniqueview,
                avg(custom_float) / 1000 as generationtime,
                avg (time_spent_ref_action)  as timespent,
                sum (cast (case when v.visit_exit_idaction_name = a.idaction_name then 1  else 0 end as int)) as bouncepages,
                max(server_time) as latest_server_time
            from mdl_analytic_log_link_visit_action a,
                mdl_analytic_log_visit v
            where ".$dateqry."
                ".$courseflt."
                and a.idvisit = v.idvisit
            group by idaction_name) t,
            mdl_analytic_log_action c
        where t.idaction_name = c.idaction )t2
    order by latest_server_time desc";

    $pages = $DB->get_records_sql($sql, array());
    $report->pages = $pages;

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;

}

function getDownloadReport ($sdate, $edate = '', $courseid, &$report = null) {
    global $DB;
    $dashboard = false;
    if ($report == null) {
        $report = new stdClass();    
    } else {
        $dashboard = true;
    }

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        // $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate->format("Y-m-d");
    }

    $rptname = 'RptDownload';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr && !$dashboard) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
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
        $courseflt .= " and custom_var_v1 = " . $courseid . " ";
    }

    $sql = "
        select
          count(*) as totaldownload,
          sum (cast (case when ".$dateqry." then 1 else 0 end as int)) as perioddownload,
          count (distinct (concat ( custom_var_v3, '-', custom_var_v1))) as uniquedownload,
          count (distinct (cast (case when ".$dateqry." then concat ( custom_var_v3, '-', custom_var_v1) else null end as varchar))) as periodunique
        from mdl_analytic_log_link_visit_action
        where custom_var_v2 = 'Download' ".$courseflt;

    $stat = $DB->get_record_sql($sql, array());
    if (!$dashboard) {
        $report->stats = $stat;
    }

    $sql = "
        select newid(), c.id, c.fullname, custom_var_v3, custom_var_v1, cnt
        from (
            select custom_var_v3, custom_var_v1, count(*) as cnt
            from mdl_analytic_log_link_visit_action
            where custom_var_v2 = 'Download' ".$courseflt." and ".$dateqry."
            group by custom_var_v3, custom_var_v1
        ) t,
          mdl_course c where c.id = t.custom_var_v1
        order by cnt desc";

    $data = $DB->get_records_sql($sql, array());
    if (!$dashboard) {
        $report->tbldata = $data;
    

        $sql = "
            select
                concat (
                  case
                    when cast (DATEPART(d, server_time) as int)  < 10
                    then concat ('0', DATEPART(d, server_time))
                    else concat (DATEPART(d, server_time), '')
                  end,
                  '-',
                  case
                    when cast (DATEPART(MM, server_time) as int)  < 10
                    then concat ('0', DATEPART(MM, server_time))
                    else concat (DATEPART(MM, server_time), '')
                  end,
                  '-',
                  DATEPART(yy, server_time)
                ) as date,
                count(*) as count
                from mdl_analytic_log_link_visit_action
                where custom_var_v2 = 'Download'
            and ".$dateqry."
            ".$courseflt."
            group by
              concat (
                case
                  when cast (DATEPART(d, server_time) as int)  < 10
                  then concat ('0', DATEPART(d, server_time))
                  else concat (DATEPART(d, server_time), '')
                end,
                '-',
                case
                  when cast (DATEPART(MM, server_time) as int)  < 10
                  then concat ('0', DATEPART(MM, server_time))
                  else concat (DATEPART(MM, server_time), '')
                end,
                '-',
                DATEPART(yy, server_time)
              ) ";

        $recs = $DB->get_records_sql($sql, array());

        $tempitem = new stdClass();
        $tempitem ->date = '';
        $tempitem->count = 0;

        $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
        $report->graphdata = json_encode($ary);

        setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));

    } else {
        $report->tbldatadl = $data;
    }

    return $report;
}

function getSearchReport ($sdate, $edate = '') {
    global $DB;
    $report = new stdClass();
    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        // $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate->format("Y-m-d");
    }

    $rptname = 'RptSearch';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, 1, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
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

    $sql = "
        select
            count(*) as totalsearch,
            sum (cast (case when ".$dateqry." then 1 else 0 end as int)) as periodsearch,
            count (distinct (custom_var_v3)) as uniquesearch,
            count (distinct (cast (case when ".$dateqry." then custom_var_v3 else null end as varchar))) as periodunique
        from mdl_analytic_log_link_visit_action
        where custom_var_v2 = 'Search'";

    $stat = $DB->get_record_sql($sql, array());
    $report->stats = $stat;

    $sql = "
        select custom_var_v3, count(*) as cnt from mdl_analytic_log_link_visit_action
        where custom_var_v2 = 'Search' and ".$dateqry."
        group by custom_var_v3
        order by cnt desc";

    $data = $DB->get_records_sql($sql, array());
    $report->tbldata = $data;

    $sql = "
        select
            concat (
              case
                when cast (DATEPART(d, server_time) as int)  < 10
                then concat ('0', DATEPART(d, server_time))
                else concat (DATEPART(d, server_time), '')
              end,
              '-',
              case
                when cast (DATEPART(MM, server_time) as int)  < 10
                then concat ('0', DATEPART(MM, server_time))
                else concat (DATEPART(MM, server_time), '')
              end,
              '-',
              DATEPART(yy, server_time)
            ) as date,
            count(*) as count
            from mdl_analytic_log_link_visit_action
            where custom_var_v2 = 'Search'
        and ".$dateqry."
        group by
          concat (
            case
              when cast (DATEPART(d, server_time) as int)  < 10
              then concat ('0', DATEPART(d, server_time))
              else concat (DATEPART(d, server_time), '')
            end,
            '-',
            case
              when cast (DATEPART(MM, server_time) as int)  < 10
              then concat ('0', DATEPART(MM, server_time))
              else concat (DATEPART(MM, server_time), '')
            end,
            '-',
            DATEPART(yy, server_time)
          ) ";

    $recs = $DB->get_records_sql($sql, array());
    $tempitem = new stdClass();
    $tempitem ->date = '';
    $tempitem->count = 0;

    $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
    $report->graphdata = json_encode($ary);

    setReport($rptname, 1, $startdate, json_encode($report));
    return $report;
}

function getEngagementReport ($sdate, $edate = '', $courseid) {
    global $DB;
    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        // $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate->format("Y-m-d");
    }

    $rptname = 'RptEngagement';
    $rpt  = getReport ($rptname, $courseid, $startdate);
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " visit_first_action_time >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }

    $where = " where ".$dateqry. " ";
    if ($courseid > 1) {
        $where .= " and idvisit in(select distinct(idvisit) from mdl_analytic_log_link_visit_action where custom_var_v1 = ".$courseid.") ";
    }

    $sql = "
        select
            sum(visitor_returning) as repeatvisit,
            count (distinct (cast (case when visitor_returning = 1 then user_id else null end as varchar))) as repeatunique,
            sum (cast (case when visitor_returning = 1 then visit_total_actions else 0 end as int)) as repeatactions,
            sum (cast (case when visitor_returning = 1 then visit_total_time else 0 end as int)) as repeatactiontime,
            sum (cast (case when custom_var_v1 ='Web' then visitor_returning else 0  end as int)) as repeatwebvisit,
            sum (cast (case when custom_var_v1 ='Mobile' then visitor_returning else 0  end as int)) as repeatmobilevisit,
            sum (cast (case when custom_var_v1 ='Web' and visitor_returning = 1 then visit_total_actions else 0  end as int)) as repeatwebactions,
            sum (cast (case when custom_var_v1 ='Mobile' and visitor_returning = 1 then visit_total_actions else 0  end as int)) as repeatmobileactions,
            sum (cast (case when custom_var_v1 ='Web' and visitor_returning = 1 then visit_total_time else 0  end as int)) as repeatwebtime,
            sum (cast (case when custom_var_v1 ='Mobile' and visitor_returning = 1 then visit_total_time else 0  end as int)) as repeatmobiletime
        from mdl_analytic_log_visit ".$where;

    $stat = $DB->get_record_sql($sql, array());
    $report->stats = $stat;

    $sql = "
        select p.ordering, p.display, count(*) as cnt
        from mdl_analytic_log_visit v, mdl_analytic_periods p
        ".$where."
          and p.name = 'VisitDuration'
          and v.visit_total_time > p.start
          and v.visit_total_time <= p.[end]
        group by p.ordering, p.display
        order by p.ordering";

    $visitduration = $DB->get_records_sql($sql, array());
    $report->visitduration = $visitduration;

    $sql = "
        select p.ordering, p.display,  count(*) as cnt
        from mdl_analytic_log_visit v, mdl_analytic_periods p
        ".$where."
          and p.name = 'VisitPages'
          and v.visit_total_time > p.start
          and v.visit_total_time <= p.[end]
        group by p.ordering, p.display
        order by p.ordering";

    $visitpages = $DB->get_records_sql($sql, array());
    $report->visitpages = $visitpages;

    $sql = "
      select
        concat (
          case
            when cast (DATEPART(d, visit_first_action_time) as int)  < 10
            then concat ('0', DATEPART(d, visit_first_action_time))
            else concat (DATEPART(d, visit_first_action_time), '')
          end,
          '-',
          case
            when cast (DATEPART(MM, visit_first_action_time) as int)  < 10
            then concat ('0', DATEPART(MM, visit_first_action_time))
            else concat (DATEPART(MM, visit_first_action_time), '')
          end,
          '-',
          DATEPART(yy, visit_first_action_time)
        ) as date,
        sum(visitor_returning) as count
        from mdl_analytic_log_visit ".$where."
        group by
          concat (
            case
              when cast (DATEPART(d, visit_first_action_time) as int)  < 10
              then concat ('0', DATEPART(d, visit_first_action_time))
              else concat (DATEPART(d, visit_first_action_time), '')
            end,
            '-',
            case
              when cast (DATEPART(MM, visit_first_action_time) as int)  < 10
              then concat ('0', DATEPART(MM, visit_first_action_time))
              else concat (DATEPART(MM, visit_first_action_time), '')
            end,
            '-',
            DATEPART(yy, visit_first_action_time)
          ) ";

    $recs = $DB->get_records_sql($sql, array());
    $tempitem = new stdClass();
    $tempitem ->date = '';
    $tempitem->count = 0;

    $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
    $report->graphdata = json_encode($ary);

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;
}

function getLatLong ($cty) {
    global $ctynames;
    if (!$ctynames) {
        $ctynames = json_decode(file_get_contents('code/country.json'), true);
    }
    if (array_key_exists(strtoupper($cty), $ctynames)) {
        $country = $ctynames[strtoupper($cty)];
        $lat =  $country['lat'];
        $long =  $country['long'];
        return "".$lat.", ".$long;
    }
    return "-21.13, -175.2";

    /*
    if ($cty == "sg") return "1.3, 103.8";
    if ($cty == "us") return "40.3, -101.38";
    if ($cty == "in") return "40.3, -101.38";
    else return "-21.13, -175.2";
    */
}

function generateCountry () {
    $ctynames = json_decode(file_get_contents('code/names.json'), true);
    $ctycode = array_keys($ctynames);
    $ary = array();
    foreach ($ctycode as $code) {
        $country = new stdClass();
        $country->code = $code;
        $country->name = $ctynames[$code];
        $valid = false;
        while (!$valid) {
            $geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode($country->name).'&sensor=false');
            $output= json_decode($geocode);
            $country->lat = $output->results[0]->geometry->location->lat;
            $country->long = $output->results[0]->geometry->location->lng;
            if ($country->lat == null || $country->long == null) {
                if ($output->status == "ZERO_RESULTS")
                    $valid = true;
                else
                    $valid = false;
            } else {
                $valid = true;
            }
        }
        $ary[$code] = $country;
    }
    $json = json_encode($ary);
    $countryfile = "country.json";
    $fh = fopen($countryfile, 'w');
    fwrite ($fh, $json);
    fclose ($fh);
}

function getCountry ($cty) {
    global $ctynames;
    if (!$ctynames) {
        $ctynames = json_decode(file_get_contents('code/country.json'), true);
    }
    if (array_key_exists(strtoupper($cty), $ctynames)) {
        $country = $ctynames[strtoupper($cty)];
        return $country['name'];
    }
    return "Unknown";
}

function safediv ($val1, $val2) {
    if ($val2 == 0)
        return 0;
    return $val1 / $val2;
}
function safediv3 ($val1, $val2, $val3) {
    if ($val2 == 0 || $val3 == 0)
        return 0;
    return $val1 / $val2 / $val3;
}

function secondsToTime($seconds)
{
    // extract hours
    $hours = floor($seconds / (60 * 60));

    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);

    // extract the remaining seconds
    $divisor_for_seconds = $divisor_for_minutes % 60;
    $seconds = ceil($divisor_for_seconds);

    if ($hours > 0)
        return "". (int) $hours. " hrs ". (int) $minutes . " mins ".(int) $seconds. " secs";
    if ($minutes > 0)
        return "". (int) $minutes . " mins ".(int) $seconds. " secs";
    return "". (int) $seconds. " secs";
    /*
    // return the final array
    $obj = array(
        "h" => (int) $hours,
        "m" => (int) $minutes,
        "s" => (int) $seconds,
    );
    return $obj;
    */
}

function getVisitorLandingContent($sdate, $edate = '', $userid = 0, $courseid = 0) {
    global $DB;

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
        $starttime = $sdate->format("Y-m-d H:i:s");
    }
    if ($edate != ''){
        $enddate = $edate->format("Y-m-d");
        $endtime = $edate->format("Y-m-d H:i:s");
    }

    $rptname = 'RptVisitorLanding';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr) {
        $rpt = getReport($rptname, $courseid.$userid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
    }

    $dateqry = '';
    if ($startdate != null) {
        $dateqry = " visit_first_action_time >= '".$starttime."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " visit_first_action_time <= '".$endtime."' ";
    }
    $cseqry = '';
    if ($courseid > 0) {
        $cseqry .= " and l.custom_var_v1 = " .$courseid;
    }
    if ($userid > 0) {
        $cseqry .= " and v.userid = " .$userid;
    }

    $sql = "Select count (distinct user_id) user_d_count, count(distinct u_web) as u_web, count(distinct u_mobile) as u_mobile , count ( distinct action_d_count) as action_d_count, sum(action_count) as action_count, sum(a_web) as a_web, sum(a_mobile) as a_mobile
        from (
            select v.user_id , (case when v.custom_var_v1='Web' then v.idvisit end) as u_web, (case when v.custom_var_v1='Mobile' then v.idvisit end) as u_mobile 
            ,a.idaction as action_d_count, count(a.name) as action_count, count(case when v.custom_var_v1='Web' then 1 end) as a_web, count(case when v.custom_var_v1='Mobile' then 1 end) as a_mobile
            from mdl_analytic_log_action a
            join
            mdl_analytic_log_link_visit_action l on l.idaction_url = a.idaction or l.idaction_name = a.idaction
            join
            mdl_analytic_log_visit v on v.idvisit = l.idvisit
            where type = 1 and user_id <> '' ". $cseqry;
    if ($userid != null) {
        $sql .= ' and user_id = ' . $userid;
    }
    if ($dateqry != '') {
        $sql .= " and ".$dateqry;
    }
    $sql .= " group by v.idvisit, v.custom_var_v1, v.user_id, a.idaction
        ) t";

    $result = $DB->get_record_sql($sql);

    $ret = array("visitorNo" => array("count" => $result->u_web + $result->u_mobile , "web" => $result->u_web, "mobile" => $result->u_mobile),
        "visitorUniqueNo" => array("count" => $result->user_d_count , "web" => $result->u_web, "mobile" => $result->u_mobile),
        "actionUniqueNo" => array("count" => $result->action_d_count, "web" => $result->a_web, "mobile" => $result->a_mobile),
        "actionNo" => array("count" => $result->action_count, "web" => $result->a_web, "mobile" => $result->a_mobile));
    return $ret;
}

function getDetailsReport ($startdate = '', $enddate = '', $courseid = 0) {
    global $DB;
    $report = new stdClass();
    $bind = array();
    $contextid = 0;
    $coursesql = '';
    if ($courseid > 1) {
        $sql = "select id from {context} where contextlevel = 50 and instanceid = ".$courseid;
        $rec = $DB->get_record_sql ($sql, array());
        $contextid = $rec->id;

        $coursesql = 'and l.custom_var_v1 = '.$courseid;
    }
    $sql = "
    select *, nummodules/csecount as average from (
        select m.name,
               count(*) as nummodules,
               count(distinct(c.course)) as csecount,
                sum (cast (case when c.course = ".$courseid." then 1 else 0  end as int)) as csemodcount
        from mdl_course_modules c, mdl_modules m where c.module = m.id
        group by m.name
        UNION
        select 'forumposts' as name, count(*) as nummodules, count(distinct (d.course)) as csecount,
        sum (cast (case when d.course = ".$courseid." then 1 else 0 end as int)) as csemodcount
        from mdl_forum_posts p, mdl_forum_discussions d
        where p.discussion = d.id
        UNION
        select 'files' as name, count(*) as nummodules, count(distinct (contextid)) as csecount,
        sum (cast (case when contextid = ".$contextid." then 1 else 0 end as int)) as csemodcount
        from mdl_files
        where component = 'course' and filearea = 'repository'
    ) t where csemodcount > 0";

    $tbldata = $DB->get_records_sql($sql, $bind);

    $s1 = array();
    foreach ($tbldata as $data1) {
        array_push($s1, $data1);
    }

    $report->tbldata = $s1;

    $sql2 = "
        select cast(server_time as DATE) as date, count (distinct visit) user_d_count, count (visit) user_count, count(distinct u_web) as u_web, count(distinct u_mobile) as u_mobile,
        count(u_web) as a_web, count(u_mobile) as a_mobile, count(idaction) as a_act, count( distinct idaction) as u_act
        from (
        select distinct l.*, v.user_id , v.idvisit as visit ,
        (case when v.custom_var_v1='Web' then v.idvisit end) as u_web, (case when v.custom_var_v1='Mobile' then v.idvisit end) as u_mobile ,a.idaction as idaction
        from mdl_analytic_log_action a 
        join mdl_analytic_log_link_visit_action l on l.idaction_url = a.idaction or l.idaction_name = a.idaction 
        join mdl_analytic_log_visit v on v.idvisit = l.idvisit where type = 1 and user_id <> '' ".$coursesql."
        ) t  group by cast(server_time as DATE) order by date
    ";

    $graphdata = $DB->get_records_sql($sql2);
    $s2 = array();
    foreach ($graphdata as $data1) {
        array_push($s2, $data1);
    }


    $report->graphdata = $s2;

    return $report;
}

function getActivityCount ($rptrecs, $cid, $activity) {
    foreach ($rptrecs as $rec) {
        if ($rec->name == $activity) {
            if ($cid <= 1)
                return number_format($rec->nummodules);
            else {
                return number_format($rec->csemodcount);
            }
        }
    }
    return 0;
}

function getActivityCountAverage ($rptrecs, $cid, $activity) {
    foreach ($rptrecs as $rec) {
        //echo "<pre>";print_r($rec);echo "</pre>";
        if ($rec->name == $activity) {
            if (isset($rec->average)){
                return number_format($rec->average);
            } else {
                return (int) ($rec->nummodules / $rec->csecount);
            }
        }
    }
    if ($cid > 1 ){
        $nd = new DateTime();
        $nd->sub(new DateInterval("P30D"));
        $startdate = $nd->format("Y-m-d");        
        return getActivityCountAverage(getLandingReport($startdate)->tbldata, 1, $activity);
    } else {
        return 0;
    }
}

function getActivityDesc ($rptrecs, $cid, $activity) {
    foreach ($rptrecs as $rec) {
        if ($rec->name == $activity) {
            if ($cid <= 1)
                return "Average ". (int) ($rec->nummodules / $rec->csecount). " items per course";
        }
    }
    return "There are no activities of this type";
}

function getUnits () {
    global  $DB, $USER;
    $userid = $USER->id;
    $table = "SELECT e.id, e.name, e.visible
        FROM {course_categories} e, {course_categories_ext} f, {context} c, {role_assignments} ra, {role} r
        WHERE   e.id            = f.categoryid
        and     f.categorytype  = 'course'
        and     f.deleted         = 0
        and     f.categoryid    = c.instanceid
        and     ra.contextid    = c.id
        and     r.id            = ra.roleid
        and     contextlevel    = 40
        and     r.shortname       = 'unitadmin'
        and     userid          = " . $userid . "
        and     parent = 0";

    $data = $DB->get_records_sql($table);

    return $data;

}

function printDroplist ($data, $name) {
    $html = '<select class="select_style" name="'.$name.'" id="'.$name.'" >
         <option value="0" >--Select--</option>';
       
    foreach ($data as $key => $value) {
        $html .='<option value="'.$value->id.'">' . $value->name . '</option>';
    }
    $html .= '</select>';

    return $html;
}

function getCoursesFromUnit($category = 0) {
    global $DB, $USER;    
    return array();
}

function getCourseForumReport ($sdate, $edate, $courseid, $top = 5 , $report) {
    global $DB;
    $dashboard = false;
    if ($report == null) {
        $report = new stdClass();    
    } else {
        $dashboard = true;
    }

    $startdate = '';
    $enddate = '';
    if ($sdate != '') {
        $startdate = $sdate->format("Y-m-d");
    }
    if ($edate != ''){
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $rptname = 'RptForum';
    $today = new DateTime();
    $todaystr = $today->add(new DateInterval("P1D"))->format("Y-m-d");
    if ($enddate != $todaystr && !$dashboard) {
        $rpt = getReport($rptname, $courseid, $startdate . $enddate);
        if ($rpt !== false) {
            $report = json_decode($rpt);
            return $report;
        }
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

    $sql1 = "
        SELECT top ".$top." discussion, count, name FROM 
        (SELECT discussion, count(*) as count 
        from {forum_posts} where discussion in (select id from {forum_discussions} where course = ".$courseid.")
        group by discussion
        ) t join {forum_discussions} d on t.discussion = d.id
        order by count desc";

    $topthreads = $DB->get_records_sql($sql1, array());
    $report->topthreads = $topthreads;

    //student = 5
    $sql2 = "
        SELECT top $top t.userid, max(t.count) as count, roleid as roleid FROM
        (select userid, count(*) as count from {forum_posts} where discussion in (select id from {forum_discussions} where course = $courseid)
        group by userid) t join {role_assignments} r on t.userid = r.userid 
        join {context} ct on ct.id = r.contextid where ct.instanceid = $courseid and roleid = 5
        group by t.userid, roleid";

    $toplearners = $DB->get_records_sql($sql2, array());
    $report->toplearners = $toplearners;

    //instructors = 3
    $sql3 = "
        SELECT top $top t.userid, max(t.count) as count, roleid as roleid FROM
        (select userid, count(*) as count from {forum_posts} where discussion in (select id from {forum_discussions} where course = $courseid)
        group by userid) t join {role_assignments} r on t.userid = r.userid 
        join {context} ct on ct.id = r.contextid where ct.instanceid = $courseid and roleid = 3
        group by t.userid, roleid";

    $topinstructors = $DB->get_records_sql($sql3, array());
    $report->topinstructors = $topinstructors;

    setReport($rptname, $courseid, $startdate.$enddate, json_encode($report));
    return $report;
}

function getUserById($id)
{
    global $DB;
    $sql = "SELECT * FROM {user} where id = ?";
    return $DB->get_record_sql($sql, array($id));
}
