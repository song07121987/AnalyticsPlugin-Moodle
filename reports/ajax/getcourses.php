<?php
require_once('ajaxlib.php');

$unit = optional_param('unit', '', PARAM_INT);

echo json_encode(array(printDroplist(getCoursesFromUnit($unit), 'coursedrop1'), printDroplist(getCoursesFromUnit($unit), 'coursedrop2')));

?>