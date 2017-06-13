<?php
require('../../config.php');
require_once($CFG->dirroot.'/local/analytics/locallib.php');

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/analytics/install.php'));
$PAGE->set_title(get_string('engineinstall', 'local_analytics'));

local_analytics_install_engine_tables();

echo $OUTPUT->header();
echo $OUTPUT->notification(get_string('successinstalltables', 'local_analytics'), 'success');
echo $OUTPUT->continue_button(new moodle_url('/admin/settings.php', array('section' => 'local_analytics_settings')));
echo $OUTPUT->footer();