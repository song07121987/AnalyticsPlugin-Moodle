<?php
require_once('ajaxlib.php');

$start = optional_param('start', '', PARAM_INT);
$end = optional_param('end', '', PARAM_INT);
$userid = optional_param('userid', '', PARAM_INT);

getVisitorDetails($start, $end, $userid);
?>
