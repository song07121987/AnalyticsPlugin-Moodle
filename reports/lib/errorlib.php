<?php
require_once ('../../../../config.php');
require_once ('analyticslib.php');

function getErrorStats ($sdate, $edate, $appname = '', $version = '', $errorid = 0) {
    global $DB;

    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '')
        $startdate = $sdate->format("Y-m-d");
    if ($edate != '') {
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " errordate >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry = " errordate <= '".$enddate."' ";
    }

    // Global Stats
    $sql = "
      select
	    count(*) as totalerror,
	    sum (nonfatal) as totalnonfatal,
        count(distinct( cast (case when nonfatal = 1 then userid else null end as int))) as usernonfatal,
        count(distinct( cast (case when nonfatal = 0 then userid else null end as int))) as userfatal,
	    max (cast (errordate as date)) as errordate,
	    sum (cast (case when ".$dateqry." then 1 else 0  end as int)) as ptotalerror,
	    sum (cast (case when ".$dateqry." and nonfatal = 1 then 1 else 0  end as int)) as ptotalnonfatal,
        count(distinct( cast (case when ".$dateqry." and nonfatal = 1 then userid else null end as int))) as pusernonfatal,
        count(distinct( cast (case when ".$dateqry." and nonfatal = 0 then userid else null end as int))) as puserfatal,
	    max (cast (case when ".$dateqry." then errordate else null end as date)) as perrordate
	    from {analytic_errorlog}";

    $bind = array();
    $where = '';
    if ($appname != '') {
        $where .= " where appname = ?";
        $bind[] = $appname;
    }
    if ($version != '') {
        if ($where != '')
            $where .= " and ";
        else
            $where .= " where ";
        $where .= " [version] = ?";
        $bind[] = $version;
    }

    $rec = $DB->get_record_sql($sql.$where, $bind);

    $report->stats = $rec;

    if ($startdate != '') {
        if ($where != '')
            $where .= " and ";
        else
            $where .= " where ";
        $where .= " errordate >= '".$startdate."' ";
        // $bind[] = $startdate;
    }
    if ($enddate != '') {
        if ($where != '')
            $where .= " and ";
        else
            $where .= " where ";
        $where .= " errordate <= '".$enddate."' ";
    }

    $sql = "
      select
	    concat (
		  case
			when cast (DATEPART(d, errordate) as int)  < 10
			then concat ('0', DATEPART(d, errordate))
			else concat (DATEPART(d, errordate), '')
		  end,
		  '-',
		  case
			when cast (DATEPART(MM, errordate) as int)  < 10
			then concat ('0', DATEPART(MM, errordate))
			else concat (DATEPART(MM, errordate), '')
		  end,
		  '-',
		  DATEPART(yy, errordate)
	    ) as date, count(*) as count
	    from mdl_analytic_errorlog ".$where."
        group by
	      concat (
		    case
			  when cast (DATEPART(d, errordate) as int)  < 10
			  then concat ('0', DATEPART(d, errordate))
			  else concat (DATEPART(d, errordate), '')
		    end,
		    '-',
		    case
			  when cast (DATEPART(MM, errordate) as int)  < 10
			  then concat ('0', DATEPART(MM, errordate))
			  else concat (DATEPART(MM, errordate), '')
		    end,
		    '-',
		    DATEPART(yy, errordate)
	      ) ";

    $recs = $DB->get_records_sql($sql, $bind);
    $tempitem = new stdClass();
    $tempitem ->date = '';
    $tempitem->count = 0;
    $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
    $report->graphdata = json_encode($ary);

    if ($appname == '') {
        $sql = "
          select
			appname,
			count(*) as versioncount,
			sum(totalerror) as totalerror,
			sum(totalnonfatal) as totalnonfatal,
			sum(usernonfatal) as usernonfatal,
			sum(userfatal) as userfatal,
			max(errordate) as errordate,
			sum(ptotalerror) as ptotalerror,
			sum(ptotalnonfatal) as ptotalnonfatal,
			sum(pusernonfatal) as pusernonfatal,
			sum(puserfatal) as puserfatal,
			max(perrordate) as perrordate
		  from
			(
              select
				appname,
				[version],
				count(*) as totalerror,
				sum (nonfatal) as totalnonfatal,
				count(distinct( cast (case when nonfatal = 1 then userid else null end as int))) as usernonfatal,
				count(distinct( cast (case when nonfatal = 0 then userid else null end as int))) as userfatal,
				max(errordate) as errordate,

				sum (cast (case when ".$dateqry." then 1 else 0  end as int)) as ptotalerror,
				sum (cast (case when ".$dateqry." and nonfatal = 1 then 1 else 0  end as int)) as ptotalnonfatal,
				count(distinct( cast (case when ".$dateqry." and nonfatal = 1 then userid else null end as int))) as pusernonfatal,
				count(distinct( cast (case when ".$dateqry." and nonfatal = 0 then userid else null end as int))) as puserfatal,
				max (cast (case when ".$dateqry." then errordate else null end as date)) as perrordate
			  from {analytic_errorlog}
			  group by appname, [version]
            ) t
            where ptotalerror > 0
          group by appname
        ";

        $tbldata = $DB->get_records_sql($sql, $bind);
        $report->tbldata = $tbldata;
        $report->fields = array ('Application Name','Versions','Number of issues (Non Fatal / Fatal)', 'Affected Users (Non Fatal)', 'Affected Users (Fatal)', 'Total Issues (All Time)', 'Last Issue Date');
        $report->datafields = array ('appname', 'versioncount', 'ptotalerror', 'pusernonfatal', 'puserfatal', 'totalerror', 'perrordate' );

    } else if ($appname != '' && $version == '') {
        $sql = "
            select
                [version],
                appname,
                count(*) as totalerror,
                sum (nonfatal) as totalnonfatal,
                count(distinct( cast (case when nonfatal = 1 then userid else null end as int))) as usernonfatal,
                count(distinct( cast (case when nonfatal = 0 then userid else null end as int))) as userfatal,
                max(errordate) as errordate,

                sum (cast (case when ".$dateqry." then 1 else 0  end as int)) as ptotalerror,
                sum (cast (case when ".$dateqry."  and nonfatal = 1 then 1 else 0  end as int)) as ptotalnonfatal,
                count(distinct( cast (case when ".$dateqry." and nonfatal = 1 then userid else null end as int))) as pusernonfatal,
                count(distinct( cast (case when ".$dateqry." and nonfatal = 0 then userid else null end as int))) as puserfatal,
                max (cast (case when ".$dateqry." then errordate else null end as date)) as perrordate
            from {analytic_errorlog}
			where appname = ?
            group by appname, [version]
            order by [version]
            ";

        $bind = array ($appname);
        $tbldata = $DB->get_records_sql($sql, $bind);
        $report->tbldata = $tbldata;
        $report->fields = array ('Application Name','Version','Number of issues (Non Fatal / Fatal)', 'Affected Users (Non Fatal)', 'Affected Users (Fatal)', 'Total Issues (All Time)', 'Last Issue Date');
        $report->datafields = array ('appname', 'version', 'ptotalerror', 'pusernonfatal', 'puserfatal', 'totalerror', 'perrordate' );

    } else if ($appname != '' && $version != '') {
        $sql = "
          select
            max(id) as id,
            description,
            count(*) as totalerror,
            sum (nonfatal) as totalnonfatal,
            count(distinct( cast (case when nonfatal = 1 then userid else null end as int))) as usernonfatal,
            count(distinct( cast (case when nonfatal = 0 then userid else null end as int))) as userfatal,
            max(errordate) as maxdate,

            sum (cast (case when ".$dateqry." then 1 else 0  end as int)) as ptotalerror,
            sum (cast (case when ".$dateqry." and nonfatal = 1 then 1 else 0  end as int)) as ptotalnonfatal,
            count(distinct( cast (case when ".$dateqry." and nonfatal = 1 then userid else null end as int))) as pusernonfatal,
            count(distinct( cast (case when ".$dateqry." and nonfatal = 0 then userid else null end as int))) as puserfatal,
            max (cast (case when ".$dateqry." then errordate else null end as date)) as perrordate

		  from {analytic_errorlog}
		  where appname = ?
		    and version = ?
		  group by description
		  order by description
          ";
        $bind = array ($appname, $version);
        $tbldata = $DB->get_records_sql($sql, $bind);
        $report->tbldata = $tbldata;
        $report->fields = array ('Description','Number of issues (Non Fatal / Fatal)', 'Affected Users (Non Fatal)', 'Affected Users (Fatal)', 'Total Issues (All Time)', 'Last Issue Date');
        $report->datafields = array ('description', 'ptotalerror', 'pusernonfatal', 'puserfatal', 'totalerror', 'perrordate' );
    }

    return $report;

}

function getErrorStats2 ($sdate, $edate, $appname = '', $version = '', $errorid = 0) {
    global $DB;

    $report = new stdClass();

    $startdate = '';
    $enddate = '';
    if ($sdate != '')
        $startdate = $sdate->format("Y-m-d");
    if ($edate != '') {
        $edate2 = $edate->add(new DateInterval("P1D"));
        $enddate = $edate2->format("Y-m-d");
    }

    $dateqry = '';
    if ($startdate != '') {
        $dateqry = " errordate >= '".$startdate."' ";
    }
    if ($enddate != '') {
        if ($dateqry != '')
            $dateqry .= ' and ';
        $dateqry .= " errordate <= '".$enddate."' ";
        
    }

    $bind = array();
    $where = '';

    $where = " where description in (
                    SELECT description FROM {analytic_errorlog} where id = $errorid
                ) and stack in (
                    SELECT stack FROM {analytic_errorlog} where id = $errorid
                )";

    $sql = "
      select
        concat (
          case
            when cast (DATEPART(d, errordate) as int)  < 10
            then concat ('0', DATEPART(d, errordate))
            else concat (DATEPART(d, errordate), '')
          end,
          '-',
          case
            when cast (DATEPART(MM, errordate) as int)  < 10
            then concat ('0', DATEPART(MM, errordate))
            else concat (DATEPART(MM, errordate), '')
          end,
          '-',
          DATEPART(yy, errordate)
        ) as date, count(*) as count
        from mdl_analytic_errorlog ".$where."
        group by
          concat (
            case
              when cast (DATEPART(d, errordate) as int)  < 10
              then concat ('0', DATEPART(d, errordate))
              else concat (DATEPART(d, errordate), '')
            end,
            '-',
            case
              when cast (DATEPART(MM, errordate) as int)  < 10
              then concat ('0', DATEPART(MM, errordate))
              else concat (DATEPART(MM, errordate), '')
            end,
            '-',
            DATEPART(yy, errordate)
          ) ";

    $recs = $DB->get_records_sql($sql, $bind);
    $tempitem = new stdClass();
    $tempitem ->date = '';
    $tempitem->count = 0;
    $ary = getDateRangeData ($recs, $sdate, $edate, $tempitem, 'date');
    $report->graphdata = json_encode($ary);

    //echo "<pre>"; print_r($dateqry); echo "</pre>"; exit;

    if ($errorid > 0 ) {

        $sql = "SELECT id, appname, version, os, manufacturer, device, userid, errordate, description, stack  from {analytic_errorlog} where description in (
                    SELECT description FROM {analytic_errorlog} where id = ?
                ) and stack in (
                    SELECT stack FROM {analytic_errorlog} where id = ?
                ) and " . $dateqry;


        $listingdata = $DB->get_records_sql($sql, array($errorid, $errorid));

        if (!$listingdata) {
            $report->description = '';
            $report->stack = '';    
        } else {
            $report->description = reset($listingdata)->description;
            $report->stack = reset($listingdata)->stack;
        }

        $devices = array();
        $os = array();
        
        foreach ($listingdata as $key => $value) {
       
            if (!array_key_exists($value->device, $devices)) {
                $devices[$value->device] = 0;
                $devices[$value->device]++;
            } else if (array_key_exists($value->device, $devices)) {
                $devices[$value->device]++;
            }
            
            if (!array_key_exists($value->os, $os)) {
                $os[$value->os] = 0;
                $os[$value->os]++;            
            } else if (array_key_exists($value->os, $os)) {
                $os[$value->os]++;
            }
        }

        arsort($devices);
        arsort($os);


        $top3d = array_slice($devices, 0, 3);
        $top3o = array_slice($os, 0, 3);

        $report->top3d = $top3d;
        $report->top3o = $top3o;
        $report->errorcount = sizeof($listingdata);

        $sql2 = "SELECT userid, max(errordate) lastoccured, u.firstname, u.idnumber, count(*) as occurence from {analytic_errorlog} e
                 JOIN mdl_user u on userid = u.id
                 where e.description in (
                    SELECT description FROM {analytic_errorlog}  where id = ?
                ) and e.stack in (
                    SELECT stack FROM {analytic_errorlog}  where id = ?
                ) and " . $dateqry . " group by userid, u.firstname, u.idnumber";

        $userdata = $DB->get_records_sql($sql2, array($errorid, $errorid));

        $report->userdata = $userdata;

    }


    return $report;
}