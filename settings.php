<?php
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/analytics/lib/adminlib.php');

if(is_siteadmin()) {
	
	$ADMIN->add('localplugins', new admin_category('analytics', new lang_string('pluginname', 'local_analytics')));
	
    $settings = new admin_settingpage('local_analytics_settings', get_string('settings', 'local_analytics'));
    
	$setting = new admin_setting_configcheckbox(
		'local_analytics/enableerrorlogging', 
		get_string('settings_enableerrorlogging', 'local_analytics'), 
		'', 
		false, 
		true, 
		false
	);
	$settings->add($setting);
	
	$setting = new admin_setting_configcheckbox(
		'local_analytics/enabletracking', 
		get_string('settings_enabletracking', 'local_analytics'), 
		'', 
		false, 
		true, 
		false
	);
	$settings->add($setting);
	
    $setting = new admin_setting_local_analytics_engine_database_tables_installed(
		'local_analytics/enginedbtablesinstalled',
		get_string('setting_enginedbtablesinstalled', 'local_analytics'),
		'',
		'');
    $settings->add($setting);
	
	$setting = new admin_setting_configtext(
		'local_analytics/sitename', 
		get_string('setting_sitename', 'local_analytics'),
		'', 
		'Pencil');
    $settings->add($setting);
	
	$setting = new admin_setting_configtext(
		'local_analytics/redishost', 
		get_string('setting_redishost', 'local_analytics'),
		'', 
		'127.0.0.1');
    $settings->add($setting);
	
	$setting = new admin_setting_configtext(
		'local_analytics/redisport',
		get_string('setting_redisport', 'local_analytics'),
		'', 
		'6379');
    $settings->add($setting);
	
	$choices = array();
	for($i=1;$i<=16;$i++) {
		$choices[$i] = $i;
	}
	$setting = new admin_setting_configselect(
		'local_analytics/redisdatabase',
		get_string('setting_redisdatabase', 'local_analytics'),
		'',
		2,
		$choices
	);
	$settings->add($setting);
	
	$setting = new admin_setting_configtext(
		'local_analytics/redispassword',
		get_string('setting_redispassword', 'local_analytics'),
		'', 
		'');
    $settings->add($setting);
	
	$setting = new admin_setting_configcheckbox(
		'local_analytics/useredissentinel', 
		get_string('settings_useredissentinel', 'local_analytics'), 
		'', 
		false, 
		true, 
		false
	);
	$settings->add($setting);
	
	$setting = new admin_setting_configtext(
		'local_analytics/redissentinelmastername',
		get_string('setting_redissentinelmastername', 'local_analytics'),
		'', 
		'');
    $settings->add($setting);
	
	$setting = new admin_setting_configcheckbox(
		'local_analytics/showenhancedanalytics', 
		get_string('settings_showenhancedanalytics', 'local_analytics'), 
		'', 
		false, 
		true, 
		false
	);
	$settings->add($setting);
	
	$ADMIN->add('analytics', $settings);
	
	$settings = new admin_externalpage(
		'local_analytics_updateconfiguration', 
		get_string('settings_updateconfiguration', 'local_analytics'),
		new moodle_url('/local/analytics/updateconfig.php'));
	$ADMIN->add('analytics', $settings);
	
	$settings = new admin_externalpage(
		'local_analytics_testconfiguration', 
		get_string('settings_testconfiguration', 'local_analytics'),
		new moodle_url('/local/analytics/testconfig.php'));
	$ADMIN->add('analytics', $settings);
	
	$settings = new admin_externalpage(
		'local_analytics_cleardatabase', 
		get_string('settings_cleardatabase', 'local_analytics'),
		new moodle_url('/local/analytics/clear.php'));
	$ADMIN->add('analytics', $settings);
	
	$settings = new admin_externalpage(
		'local_analytics_runloadtest', 
		get_string('settings_runloadtest', 'local_analytics'),
		new moodle_url('/local/analytics/loadtest.php'));
	$ADMIN->add('analytics', $settings);
	
	$settings = new admin_externalpage(
		'local_analytics_testerror', 
		get_string('settings_testerror', 'local_analytics'),
		new moodle_url('/local/analytics/generateerror.php'));
	$ADMIN->add('analytics', $settings);
}