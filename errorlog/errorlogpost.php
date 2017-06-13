<?php
require_once('../../../config-analytics.php');
require_once('ipresolver.php');
global $CFG;

$data = json_decode(file_get_contents('php://input'), true);
if ($data == '') return;
if ($CFG->mq_analyticsdb == "MSSQL") {
    require_once ('errordb.php');
    $res = storeErrorsInDB($data);
} else {
    require_once('errormongo.php');
    $res = storeErrorInMongo($data);
}
if ($res)
    echo "Success";
else
    echo "Failure";
?>