<?php
require_once('../../../../config.php');
require_once($CFG->dirroot . '/administration/lib.php');

$unit = optional_param('unit', '', PARAM_INT);

$pastweek = strtotime("-1 week");
$ret = getDashboardCounts($USER->id, $pastweek, $unit);

echo json_encode($ret);

?>