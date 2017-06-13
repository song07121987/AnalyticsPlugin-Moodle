<?php
defined('MOODLE_INTERNAL') || die();

function local_analytics_get_engine_tables() {
	global $CFG;
	
	$filepath = $CFG->dirroot.'/local/analytics/schema/mssql/analyticsschema.sql';
	$contents = file_get_contents($filepath);
	
	$create_table_statements = array();
	$arr = explode("/****** Object:", $contents);
	foreach($arr as $object) {
		$query = substr($object, strpos($object, '******/'));
		if(strpos($query, 'CREATE TABLE') !== false) {
			$create_table_statements[] = str_replace("******/\n", '', $query);
		}
	}
	
	$tables = array();
	foreach($create_table_statements as $statement) {
		preg_match('/CREATE TABLE (.*?)\]\(/', $statement, $match);
		$tables[] = str_replace("[dbo].[mdl_", '', $match[1]);
	}
	return $tables;
}

function local_analytics_engine_db_installed() {
    global $DB, $CFG;
    
    $dbman = $DB->get_manager();
    
    $tables = local_analytics_get_engine_tables();
    
    foreach($tables as $tablename) {
        $table = new xmldb_table($tablename);
        if(! $dbman->table_exists($table)) {
            return false;
        }
    }
    return true;
}

function local_analytics_clear_engine_db() {
	global $DB;
	
	$tables_to_clear = array('analytic_access', 'analytic_goal',
		'analytic_log_action', 'analytic_log_conversion', 'analytic_log_conversion_item',
		'analytic_log_link_visit_action', 'analytic_log_profiling', 'analytic_log_visit',
		'analytic_logger_message', 'analytic_report', 'analytic_segment', 'analytic_session',
		'analytic_site_setting', 'analytic_site_url', 'analytic_user_dashboard', 'analytic_user_language');
	foreach($tables_to_clear as $tablename) {
		$DB->delete_records($tablename);
	}
}

function local_analytics_install_engine_tables() {
    global $CFG;
    
    if($CFG->dbtype == 'sqlsrv') {
        local_analytics_install_engine_mssql_tables();
    }
    if($CFG->dbtype == 'mysqli') {
        local_analytics_install_engine_mysql_tables();
    }
}

function local_analytics_install_engine_mssql_tables() {
    global $CFG, $DB;
    
	$filepath = $CFG->dirroot.'/local/analytics/schema/mssql/analyticsschema.sql';
	$contents = file_get_contents($filepath);
	
	$contents = str_replace("\r\n", "\n", $contents);
	$contents = str_replace("[dbo].[mdl_analytic_", "[dbo].[{$CFG->prefix}analytic_", $contents);
	$contents = str_replace(' [tinyint] ', ' [int] ', $contents);
	
	$statements = array();
	$arr = explode("/****** Object:", $contents);

	foreach($arr as $object) {
		$query = substr($object, strpos($object, "******/"));
		$statements[] = str_replace("******/\n", '', $query);
	}

	$execute_arr = array();
	foreach($statements as $statement) {
		$arr = explode("GO\n", $statement);
		foreach($arr as $execute_item) {
			if(strpos($execute_item, 'USE [') !== false) {
				continue;
			}
			if(strpos($execute_item, 'DROP CONSTRAINT [') !== false) {
				continue;
			}
			if(strpos($statement, 'DROP TABLE') !== false) {
				continue;
			}
			$execute_item = trim($execute_item);
			if($execute_item) {
				$execute_arr[] = $execute_item;
			}
		} 
	}
	foreach($execute_arr as $statement) {	
		$DB->execute($statement);
	}

	
	$filepath = $CFG->dirroot.'/local/analytics/schema/mssql/analytics.sql';
	$contents = file_get_contents($filepath);
	
	$contents = str_replace("\r\n", "\n", $contents);
	$contents = str_replace("[dbo].[mdl_analytic_", "[dbo].[{$CFG->prefix}analytic_", $contents);
	
	$basic_inserts = array();
	$statements = array();
	$arr = explode("\n", $contents);
	foreach($arr as $statement) {
		if($statement == 'GO') {
			continue;
		}
		if(strpos($statement, 'USE [') !== false) {
			continue;
		}
		if(strpos($statement, ';') !== false) {
			$basic_inserts[] = $statement;
			continue;
		}
		if(strpos($statement, '{') !== false) {
			$basic_inserts[] = $statement;
			continue;
		}
		if($statement) {
			$statements[] = $statement;
		}
	}
	
    foreach($statements as $statement) {
		$DB->execute($statement);
    }
	
	if($basic_inserts) {
		$inserts = array();
		foreach($basic_inserts as $sql_insert) {
			$parts = explode("VALUES", $sql_insert);
			
			// fields stuff
			$fields_part = trim($parts[0]);
			$values_part = trim($parts[1]);
			
			preg_match('/INSERT \[dbo\]\.\[mdl_(.*?)\] \(/', $fields_part, $match);
			$tablename = $match[1];
			
			$fields_str = substr($fields_part, strpos($fields_part, "("));
			$fields_str_parts = explode(",", $fields_str);
			
			$fields = array();
			foreach($fields_str_parts as $fields_str_part) {
				$fields[] = str_replace(array("[", "]", ")", "(", " "), '', $fields_str_part);
			}

			
			// values stuff
			$values_str = trim($values_part, '()');
			$values_str_parts = explode("',", $values_str);
			$values = array();
			foreach($values_str_parts as $value) {
				if(! is_numeric($value)) {
					$value = trim($value, " N'");
				}
				$values[] = $value;
			}
			
			$insert = new stdClass;
			foreach($fields as $i => $field) {
				$insert->{$field} = $values[$i];
			}
			$inserts[$tablename][] = $insert;
		}
		foreach($inserts as $insert_table => $insert_objects) {
			$DB->insert_records($insert_table, $insert_objects);
		}
	}
}

function local_analytics_install_engine_mysql_tables() {
    global $CFG, $DB;
    
    $filepath = $CFG->dirroot.'/local/analytics/piwik.sql';
    $contents = file_get_contents($filepath);
    $contents = preg_replace('/\/\*[\s\S]+?\*\//', '', $contents);

    $arr = explode(";", $contents);
    
    $prefix = $CFG->prefix.'analytic_';
    
    $statements = array();
    foreach($arr as $part) {
        $part = trim($part);
        if(strpos($part, 'DATABASE `') !== false) {
            continue;
        }
        if(strpos($part, 'USE `') !== false) {
            continue;
        }
        if($part) {
            $part = str_replace('`piwik_', '`'.$prefix, $part);
            $statements[] = $part;
        }
    }
	
    foreach($statements as $statement) {
        $DB->execute($statement);
    }
}