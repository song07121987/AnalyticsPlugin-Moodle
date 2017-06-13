<?php
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/analytics/locallib.php');

$confirm = optional_param('confirm', 0, PARAM_INT);
$finalconfirm = optional_param('finalconfirm', 0, PARAM_INT);

require_login();
admin_externalpage_setup('local_analytics_cleardatabase');

if($finalconfirm) {
	local_analytics_clear_engine_db();
	
	$content = $OUTPUT->notification(get_string('clearsuccess', 'local_analytics'), 'success');
	$content .= $OUTPUT->continue_button(new moodle_url('/admin/category.php', array('category' => 'analytics')));
} elseif($confirm) {
	$content = $OUTPUT->notification(get_string('clearfinalwarning', 'local_analytics'), 'warning');
	$content .= $OUTPUT->continue_button(new moodle_url('/local/analytics/clear.php', array('finalconfirm' => 1)));
} else {
	$content = $OUTPUT->notification(get_string('clearwarning', 'local_analytics'), 'warning');
	$content .= $OUTPUT->continue_button(new moodle_url('/local/analytics/clear.php', array('confirm' => 1)));
}

echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();