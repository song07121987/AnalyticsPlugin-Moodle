<?php
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
admin_externalpage_setup('local_analytics_testconfiguration');

$testurl = $CFG->wwwroot.'/local/analytics/engine/piwik.php?action_name=Learnet&idsite=1&rec=1&r=259992&h=22&m=42&s=37&url=http%3A%2F%2Flocalhost%2Fmqlp%2F&urlref=http%3A%2F%2Flocalhost%2Fmqlp%2Fblocks%2Fuser_filemanager%2Findex.php&_id=862f347e5154af94&_idts=1453560086&_idvc=1&_idn=0&_refts=0&_viewts=1453560086&send_image=0&pdf=1&qt=0&realp=0&wma=0&dir=0&fla=1&java=1&gears=0&ag=0&cookie=1&res=3840x2160&gt_ms=2172';

$redisconfig = new stdClass();
$redisconfig->host = get_config('local_analytics', 'redishost');
$redisconfig->port = get_config('local_analytics', 'redisport');
$redisconfig->password = get_config('local_analytics', 'redispassword');
$redisconfig->usesentinel = get_config('local_analytics', 'useredissentinel');
$redisconfig->database = get_config('local_analytics', 'redisdatabase');
$redisconfig->sentinelmastername = get_config('local_analytics', 'redissentinelmastername');

$testresult = array(
	'curlrequest' => false,
	'redisconnect' => false,
	'redisdatacheck' => false
);

$curl = new curl();
$curl->get($testurl);
$curl_info = $curl->get_info();
if(! $curl->get_errno() && $curl_info['http_code'] == 200) {
	$testresult['curlrequest'] = true;
	
	$redis = new Redis();
	if($redis->connect($redisconfig->host, $redisconfig->port)) {
		$testresult['redisconnect'] = true;
		
		$redis->select($redisconfig->database);
		$count = 0;
		if($keys = $redis->keys('*')) {
			$key = $keys[0];
			$count = $redis->llen($key);
		}
		if($count) {
			$testresult['redisdatacheck'] = true;
		}
	}
}

foreach($testresult as $statusname => $statusflag) {
	if($statusflag) {
		$testresult[$statusname] = '<span style="color:#3c763d">'.get_string('success', 'local_analytics').'</span>';
	} else {
		$testresult[$statusname] = '<span style="color:#a94442">'.get_string('failure', 'local_analytics').'</span>';
	}
}

echo $OUTPUT->header();
echo html_writer::div(get_string('testconfigurl', 'local_analytics', $testurl));
echo html_writer::empty_tag('br');
echo html_writer::div(get_string('testconfigcurlstatus', 'local_analytics', $testresult['curlrequest']));
echo html_writer::div(get_string('testconfigredisstatus', 'local_analytics', $testresult['redisconnect']));
echo html_writer::div(get_string('testconfigredisdatastatus', 'local_analytics', (object)array('status' => $testresult['redisdatacheck'], 'count' => $count)));
echo html_writer::empty_tag('br');
echo html_writer::start_div();
echo html_writer::link(new moodle_url('/admin/category.php', array('category' => 'analytics')), get_string('backtosettings', 'local_analytics'));
echo html_writer::link(new moodle_url('/local/analytics/testconfig.php'), get_string('testconfigrepeat', 'local_analytics'), array('style' => 'margin-left:10px'));
echo html_writer::end_div();

echo $OUTPUT->footer();