<?php
require_once('../../../config-analytics.php');
require_once('ipresolver.php');
global $CFG;
$ary = array_merge($_GET, $_POST);
if (!isset($ary["app_key"])) {
    echo "No app key defined";
    return;
}
unset ($ary["app_key"]);
$ary["ip"] = get_client_ip();

if ($CFG->mq_analyticsdb == "MSSQL") {
    require_once ('errordb.php');
    $res = storeErrorInDB($ary);
} else {
    require_once('errormongo.php');
    $res = storeErrorInMongo($ary);
}
if ($res)
    echo "Success";
else
    echo "Failure";
?>