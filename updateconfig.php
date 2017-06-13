<?php
use Piwik\Ini\IniReader;
use Piwik\Ini\IniWriter;

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/analytics/locallib.php');

require_login();
admin_externalpage_setup('local_analytics_updateconfiguration');

echo $OUTPUT->header();

if(! local_analytics_engine_db_installed()) {
    print_error('error_enginenotinstalled', 'local_analytics', new moodle_url('/admin/settings.php', array('section' => 'local_analytics_settings')));
}

$local_analytics_config = get_config('local_analytics');

$DB->execute("UPDATE {analytic_site} SET name='{$local_analytics_config->sitename}', main_url='{$CFG->wwwroot}'");
$DB->execute("UPDATE {analytic_option} SET option_value='{$CFG->wwwroot}/local/analytics/engine/' WHERE option_name='piwikUrl'");

if($record = $DB->get_record('analytic_option', array('option_name' => 'Plugin_QueuedTracking_Settings'))) {

    $option_value = unserialize($record->option_value);
    
    $option_value['useSentinelBackend'] = $local_analytics_config->useredissentinel;
    $option_value['sentinelMasterName'] = $local_analytics_config->redissentinelmastername;
    $option_value['redisHost'] = $local_analytics_config->redishost;
    $option_value['redisPort'] = $local_analytics_config->redisport;
    $option_value['redisDatabase'] = $local_analytics_config->redisdatabase;
    $option_value['redisPassword'] = $local_analytics_config->redispassword;

    $DB->execute("UPDATE {analytic_option} SET option_value=? WHERE option_name='Plugin_QueuedTracking_Settings'", array(serialize($option_value)));

}

/********************************************/

require_once($CFG->dirroot.'/local/analytics/engine/vendor/piwik/ini/src/iniReader.php');
require_once($CFG->dirroot.'/local/analytics/engine/vendor/piwik/ini/src/iniWriter.php');

$inifile = $CFG->dirroot.'/local/analytics/engine/config/config.ini.php';

$ini_reader = new IniReader();
$ini_arr = $ini_reader->readFile($inifile);

$ini_arr['database']['host'] = $CFG->dbhost;
$ini_arr['database']['username'] = $CFG->dbuser;
$ini_arr['database']['password'] = $CFG->dbpass;
$ini_arr['database']['dbname'] = $CFG->dbname;
$ini_arr['database']['tables_prefix'] = $CFG->prefix.'analytic_';
if(isset($CFG->dboptions['dbport']) and ! empty($CFG->dboptions['dbport'])) {
	$ini_arr['database']['port'] = $CFG->dboptions['dbport'];
} else {
	if($CFG->dbtype == 'sqlsrv') {
		$ini_arr['database']['port'] = 1433;
	} elseif($CFG->dbtype == 'mysqli') {
		$ini_arr['database']['port'] = 3306;
	}
}
if($CFG->dbtype == 'sqlsrv') {
	$ini_arr['database']['adapter'] = 'PDO\MSSQL';
} elseif($CFG->dbtype == 'mysqli') {
	$ini_arr['database']['adapter'] = 'MYSQLI';
}
$ini_arr['General']['login_logout_url'] = $CFG->wwwroot;
$ini_arr['General']['trusted_hosts'] = $CFG->wwwroot;

//print_object($ini_arr);

$ini_writer = new IniWriter();
$ini_writer->writeToFile($inifile, $ini_arr, "; <?php exit; ?> DO NOT REMOVE THIS LINE\n");


/********************************************/

$filepath = $CFG->dirroot.'/local/analytics/engine/core/Config/ClientConfigCopy.php';

$lines = file($filepath);
foreach($lines as $linenum => $linecontents) {
	if(strpos($linecontents, 'const PIWIK_URL') !== false) {
		$lines[$linenum] = "\tconst PIWIK_URL = '{$CFG->wwwroot}/local/analytics/engine/';\n";
	}
}
$contents = implode("", $lines);
file_put_contents($filepath, $contents);

//print_object($lines);


echo $OUTPUT->notification(get_string('analyticsconfupdated', 'local_analytics'), 'success');
echo $OUTPUT->continue_button(new moodle_url('/admin/search.php'));
echo $OUTPUT->footer();
