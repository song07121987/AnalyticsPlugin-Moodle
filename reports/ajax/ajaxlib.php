<?php
require_once('../../../../config.php');

function getVisitorList($start='', $end='', $userid='') {
	$columns = array(
				array('db' => 'idvisit', 'dt' => 0, 'skip' => 1),	
                array('db' => 'date', 'dt' => 1),	
                array('db' => 'name', 'dt' => 2),
                array('db' => 'browser', 'dt' => 3, 'formatter' => function ($d, $row, $idents) {
                	return getImage($d, $idents);
            	}),
                array('db' => 'os', 'dt' => 4, 'formatter' => function ($d, $row, $idents) {
                	return getImage($d, $idents);
            	}),
                array('db' => 'country', 'dt' => 5, 'formatter' => function ($d, $row, $idents) {
                	return getImage($d, $idents, true);
            	}),
                array('db' => 'noofactions', 'dt' => 6),
                array('db' => 'totaltime', 'dt' => 7, 'formatter' => function ($d, $row, $idents) {
                	return convertTime($d);
            	}),
                array('db' => 'accesstype', 'dt' => 8)
            );
    $dateqry = '';

    if ($start != '') {
        $start1 = substr($start,0,10);
        $startD = new DateTime("@$start1");
        $startdate = $startD->format("Y-m-d H:i:s");
        $dateqry = " visit_first_action_time >= '".$startdate."' ";
    }

    
    if ($end  != '') {
        $end1 = substr($end,0,10);
        $endD = new DateTime("@$end1");
        $enddate = $endD->format("Y-m-d H:i:s");
        if ($dateqry != '') {
            $dateqry .= ' and ';
        }
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }
    

    $table = "(select distinct idvisit, CONVERT(datetime, 
               SWITCHOFFSET(CONVERT(datetimeoffset, 
                                    lv.visit_first_action_time), 
                            DATENAME(TzOffset, SYSDATETIMEOFFSET()))) 
       AS date, u.firstname as name, config_browser_name as browser, config_os as os,location_country as country, visit_total_actions as noofactions,  visit_total_time as totaltime, custom_var_v1 as accesstype  
       from {analytic_log_visit} lv left join {user} u on lv.user_id=u.id where user_id <> '' ";
    if ($userid != '') {
        $table .= " and user_id = " .$userid;
    }   
    if ($dateqry != '') {
        $table .= " and " . $dateqry;
    }
    $table .= ") t";
    $sql = "idvisit, date, name, browser, os, country, noofactions, totaltime, accesstype";

    echo json_encode(simpleq(getAllVars(), $sql, '', 'idvisit', $columns, NULL, '', '', $table));
}

function getVisitorDetails($start='', $end='', $userid='') {
	$columns = array(
                array('db' => 'date', 'dt' => 0, 'skip' => 1, 'ident' => 'date'),	
                array('db' => 'ip', 'dt' => 1, 'skip' => 1, 'ident' => 'ip'),
                array('db' => 'uid', 'dt' => 2, 'skip' => 1, 'ident' => 'uid'),
                array('db' => 'country', 'dt' => 3, 'skip' => 1, 'ident' => 'country'),
                array('db' => 'accesstype', 'dt' => 4, 'formatter' => function ($d, $row, $idents) {
                	return basicInfo($d, $idents);
            	}),
            	array('db' => 'browser', 'dt' => 5, 'skip' => 1, 'ident' => 'browser'),
                array('db' => 'os', 'dt' => 6, 'skip' => 1, 'ident' => 'os'),
                array('db' => 'device', 'dt' => 7, 'formatter' => function ($d, $row, $idents) {
                	return basicInfo2($d, $idents);
            	}),
            	array('db' => 'totaltime', 'dt' => 8, 'skip' => 1, 'ident' => 'totaltime'),
            	array('db' => 'idvisit', 'dt' => 9, 'formatter' => function ($d, $row, $idents) {
                	return basicInfo3($d, $idents);
            	})
            );

    $dateqry = '';

    if ($start != '') {
        $start1 = substr($start,0,10);
        $startD = new DateTime("@$start1");
        $startdate = $startD->format("Y-m-d H:i:s");
        $dateqry = " visit_first_action_time >= '".$startdate."' ";
    }

    
    if ($end  != '') {
        $end1 = substr($end,0,10);
        $endD = new DateTime("@$end1");
        $enddate = $endD->format("Y-m-d H:i:s");
        if ($dateqry != '') {
            $dateqry .= ' and ';
        }
        $dateqry .= " visit_first_action_time <= '".$enddate."' ";
    }

    $table = "(select idvisit, CONVERT(datetime, 
               SWITCHOFFSET(CONVERT(datetimeoffset, 
                                    lv.visit_first_action_time), 
                            DATENAME(TzOffset, SYSDATETIMEOFFSET()))) 
       AS date, user_id as uid, config_browser_name as browser, config_os as os,location_country as country, location_ip as ip, config_device_type as device, visit_total_actions as noofactions,  visit_total_time as totaltime, custom_var_v1 as accesstype 
       from {analytic_log_visit} lv left join {user} u on lv.user_id=u.id where user_id <> '' ";
    if ($userid != '') {
        $table .= " and user_id = " .$userid;
    }
    if ($dateqry != '') {
        $table .= " and " . $dateqry;
    }
    $table .= ") t";

    $sql = "idvisit, date, uid, browser, os, country, ip, device, noofactions, totaltime, accesstype";

    echo json_encode(simpleq(getAllVars(), $sql, '', 'idvisit', $columns, NULL, '', '', $table));
}

function basicInfo($d, $idents){
	$idvisit = getIdent('key', $idents, 0);
    $date = getIdent('date', $idents, 0);
    $ip = getIdent('ip', $idents, 0);
    $uid = getIdent('uid', $idents, 0);
    $country = getIdent('country', $idents, 0);

    $ret = '<ul>
              <li> <strong>'.$date.' </strong> 
                <span> IP:: '.$ip.'</span></li>
              <li>'.$uid.' <span><img src="dist/img/browser/user.png">'.getImage($country, null, true).'</span></li>
              <li>Access Type : '.$d.' <span>Direct Entry</span></li>
            </ul>';
    return $ret;
}

function basicInfo2($d, $idents){
    $browser = getIdent('browser', $idents, 0);
    $os = getIdent('os', $idents, 0);
    $device = $d;

    $ret = getImage($browser, null).getImage($os, null).getImage($device, null);
    return $ret;
}

function basicInfo3($d, $idents){
    global $DB;
	$totaltime = getIdent('totaltime', $idents, 0);
	$sql = 'select a.idaction, a.name, b.title, server_time, time_spent_ref_action, LEAD(time_spent_ref_action) OVER (ORDER BY server_time) as time_on_page, custom_float as page_generate_time from
			(
			select distinct idaction, idaction_name, name, server_time, time_spent_ref_action, custom_float from {analytic_log_action} a 
			join
			{analytic_log_link_visit_action} l on l.idaction_url = a.idaction or l.idaction_name = a.idaction
			where idvisit = ? and type = 1 ) a 
			left join 
			(
			select idaction, name as title from {analytic_log_action} a 
			join
			{analytic_log_link_visit_action} l on l.idaction_url = a.idaction or l.idaction_name = a.idaction
			where idvisit = ? and type = 4 ) b on a.idaction_name = b.idaction
			order by server_time
			';

	$results = $DB->get_records_sql($sql, array($d, $d));
	$ret = "<ul>";
	$ret .= '<strong>'.count($results).' Actions - '.convertTime($totaltime).'</strong> '.$d;
	$counter = 1;
	foreach ($results as $result) {
		$ret .= '<li>'.$counter++.'. '.$result->title;
		$ret .= '<span class="visitor-log-actions-link"><a href="http://'.$result->name.'">'.$result->name.'</a></span></li>';
	}
	$ret .= "</ul>";

	return $ret;
}

function getIdent($key, $idents, $default)
{
    foreach ($idents as $ident) {
        if ($ident[0] == $key)
            return $ident[1];
    }
    return $default;
}

function getImage($d, $idents, $isCountry = false) {
	$imageMap = array(                
                'Android' => "dist/img/browser/android.png",
                'Linux' => "dist/img/browser/linux.png",
                'WIN' => "dist/img/browser/windows.png",
                'CH' => "dist/img/browser/chrome.png",
                'FF' => "dist/img/browser/firefox.png",
                'Opera' => "dist/img/browser/opera.png",
                0 => "dist/img/browser/desktop.png"
                );
	if (array_key_exists($d,$imageMap))
		$imgsrc = '<img src="'.$imageMap[$d].'">';
	else 
		$imgsrc = $d;
	if ($isCountry) {
		$ctry = "dist/img/country-flag/";
		$imgsrc = '<img src="'.$ctry.$d.'.png">';
	}
	return $imgsrc;
}

function convertTime($d) {
	return date('H:i', mktime(0,$d));
}

function getAllVars() {
    $ary = array_merge ($_POST,$_GET);
    return $ary;
}

function simpleq($request, $fields, $table, $primarykey, $columns, $joinQuery = NULL, $extraWhere = '', $groupBy = '', $tablequery, $verifybound=true)
{
    global $DB;

    if($verifybound==true){
        if (!isset($request['start']) || !isset($request['length']))
            return 0;
        if ($request['length'] > 100)
            return 0;
    }

    $bindings = array();
    $limit = limit($request, $columns);
    $order = order($request, $columns);
    $where = filter($request, $columns, $bindings);

    if ($extraWhere) {
        $extraWhere = ($where) ? ' AND ' . $extraWhere : ' WHERE ' . $extraWhere;
        $where .= $extraWhere;
    }

    $groupBy = ($groupBy) ? ' GROUP BY ' . $groupBy . ' ' : '';
    $fieldsql = ($fields) ? $fields : implode(", ", pluck($columns, 'db'));

    $includewhere = 0;
    if ($table == '') {
        $tablesql = $tablequery;
    } else {
        $tablesql = '{' . $table . '}';
        $includewhere = 1;
    }

    // Main query to actually get the data
    $sql = "SET NOCOUNT ON SELECT {$fieldsql} FROM $tablesql $where $order $limit";
    $data = $DB->get_records_sql($sql, $bindings);
    $sql2 = "SET NOCOUNT ON SELECT COUNT({$primarykey}) FROM (SELECT {$fieldsql} from $tablesql $where ) k";
    $recordsFiltered = $DB->count_records_sql($sql2, $bindings);

    // Total data set length
    $sql3 = "SET NOCOUNT ON SELECT COUNT({$primarykey}) FROM $tablesql ";
    if ($includewhere == 1)
        $sql3 .= $where;
    $recordsTotal = $DB->count_records_sql($sql3);
    // $recordsFiltered = $recordsTotal;

    /*  Output   */
    $draw = optional_param('draw', 0, PARAM_INT);

    return array(
        "draw" => intval($draw),
        "recordsTotal" => intval($recordsTotal),
        "recordsFiltered" => intval($recordsFiltered),
        "data" => data_output($columns, $data)
		// "sql1" => $sql,
		// "sql2" => $sql2,
		// "sql3" => $sql3
    );
}
function limit($request, $columns)
{

    $limit = '';
    if (isset($request['start']) && $request['length'] != -1) {
        $limit = "ORDER BY [LINE] OFFSET " . intval($request['start']) . " ROWS FETCH NEXT " . intval($request['length']) . " ROWS ONLY";
    }
    // limit and order conflict when using sql server.
    // so duplicate the functionality in ORDER and switch on/off as needed based on ORDER
    if (isset($request['order']) && count($request['order'])) {
        $limit = '';    // if there is an ORDER request then clear the limit
        return $limit;    // because the ORDER function will handle the LIMIT
    } else {
        return $limit;
    }
}

function order($request, $columns)
{
    $order = '';
    if (isset($request['order']) && count($request['order'])) {
        $orderBy = array();
        // $dtColumns = pluck($columns, 'dt');
        for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
            // Convert the column index into the column data property
            $columnIdx = intval($request['order'][$i]['column']);
            $requestColumn = $request['columns'][$columnIdx];
            // $columnIdx = array_search($requestColumn['data'], $dtColumns);
            // $column = $columns[$columnIdx];
            $column = getVisibleColumn($columns, $columnIdx);
            if ($requestColumn['orderable'] == 'true') {
                $dir = $request['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
                $orderBy[] = '[' . $column['db'] . '] ' . $dir;   // revised for SQL Server
            }
        }
        // see "static function limit" above to explain the next line.
        $order = "ORDER BY " . implode(', ', $orderBy) . " OFFSET " . intval($request['start']) . " ROWS FETCH NEXT " . intval($request['length']) . " ROWS ONLY";
    }
    return $order;
}


function pluck($a, $prop, $isJoin = false)
{
    $out = array();
    for ($i = 0, $len = count($a); $i < $len; $i++) {
        $out[] = ($isJoin && isset($a[$i]['as'])) ? $a[$i][$prop] . ' AS ' . $a[$i]['as'] : $a[$i][$prop];
    }
    return $out;
}

function getVisibleColumn($columns, $columnidx)
{
    $colid = 0;
    foreach ($columns as $column) {
        if (!isset($column['skip'])) {
            if ($columnidx == $colid) {
                return $column;
            }
            $colid++;
        }
    }
    return null;
}


function filter($request, $columns, &$bindings)
{
    $globalSearch = array();
    $columnSearch = array();
    $dtColumns = pluck($columns, 'dt');

    if (isset($request['search']) && $request['search']['value'] != '') {
        $str = $request['search']['value'];

        for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search($requestColumn['data'], $dtColumns);
            $column = $columns[$columnIdx];

            if ($requestColumn['searchable'] == 'true') {
                $binding = bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                $globalSearch[] = "" . $column['db'] . " LIKE ?";
            }
        }
    }

    // Individual column filtering
    if (isset($request['columns'])) {
        for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search($requestColumn['data'], $dtColumns);
            $column = $columns[$columnIdx];

            $str = $requestColumn['search']['value'];

            if ($requestColumn['searchable'] == 'true' && $str != '') {
                $binding = bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                $columnSearch[] = "" . $column['db'] . " LIKE ?";
            }
        }
    }

    // Combine the filters into a single string
    $where = '';

    if (count($globalSearch)) {
        $where = '(' . implode(' OR ', $globalSearch) . ')';
    }

    if (count($columnSearch)) {
        $where = $where === '' ?
            implode(' AND ', $columnSearch) :
            $where . ' AND ' . implode(' AND ', $columnSearch);
    }

    if ($where !== '') {
        $where = 'WHERE ' . $where;
    }

    return $where;
}

function bind(&$a, $val, $type)
{
    $key = $val;
    $a[] = $val;
    /*
    $key = ':binding_'.count( $a );

    $a[] = array(
        'key' => $key,
        'val' => $val,
        'type' => $type
    );
    */
    return $key;
}

function data_output($columns, $data)
{
    // print_r ($columns);
    //print_r ($data);
    $out = array();

    $rowid = 0;

    foreach ($data as $item) {
        $row = array();
        $idents = array();
        $colid = 0;
        for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
            $column = $columns[$j];
            if (isset ($column['ident'])) {
                $idents[] = array($column['ident'], $item->$columns[$j]['db']);
                // $key = $item->$columns[$j]['db'];
            }
            if (!isset($column['skip'])) {
                // Is there a formatter?
                if (isset($column['formatter'])) {
                    $row[$colid] = $column['formatter']($item->$column['db'], $rowid, $idents);
                } else {
                    $row[$colid] =
                        $item->$columns[$j]['db'];
                }
                $colid++;
            }
        }

        $out[] = $row;
        $rowid++;
    }
    return $out;
}

function printDroplist ($data, $name) {
    $event = '';
    if ($name == 'coursedrop1' || $name == 'coursedrop2') {
        $event = 'onchange="updateData(this.value, \''.$name.'\')"';
    } 
    $html = '<select class="select_style" name="'.$name.'" '.$event.' id="'.$name.'" >';
    if ($name == 'coursedrop1') {
        $html .= '<option value="1" >View Aggregate</option>';
    } else {
        $html .= '<option value="0" >--Select--</option>';
    }
         
        
    foreach ($data as $key => $value) {
        $html .='<option value="'.$value->id.'">' . $value->name . '</option>';
    }
    $html .= '</select>';

    return $html;
}

function getCoursesFromUnit($category = 0) {
    global $DB, $USER;

    if ($category == 0) {
        return array();
    }

    $userid = $USER->id;

    $table = "SELECT distinct e.id,  o.parentcourse  as category, e.fullname as name, f.fullname, e.visible, e.id as rid, e.shortname
                        FROM {course} e ,{course_options2} o,{course} f
                        WHERE   e.id            = o.courseid
                        and     f.id            = o.parentcourse
                        and     o.coursetype    ='child'
                        and     deleted         = 0
                        and     e.category  = " . $category . "
                        and     e.category in (select distinct e.id
                                   from {course_categories} e, {role_assignments} ra, {context} t
                            where ra.contextid      = t.id
                            and t.contextlevel      = 40
                            and t.instanceid        = e.id
                            and userid              = " . $userid . "
                                  )
                                                  
                        ";

    $data = $DB->get_records_sql($table);


    return $data;
}