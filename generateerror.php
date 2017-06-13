<?php
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$generate = optional_param('generate', 0, PARAM_INT);

require_login();
admin_externalpage_setup('local_analytics_testerror');

if($generate) {
	$a = 1/0;
}

$totalerrors = $DB->count_records('analytic_errorlog');

echo $OUTPUT->header();
echo html_writer::div(get_string('totalerrors', 'local_analytics', $totalerrors));
echo html_writer::empty_tag('br');
echo html_writer::div(get_string('generateerrornote', 'local_analytics'));
echo html_writer::empty_tag('br');
echo html_writer::div(html_writer::link(new moodle_url('/local/analytics/generateerror.php', array('generate' => 1)), get_string('generateerror', 'local_analytics')));
echo $OUTPUT->footer();