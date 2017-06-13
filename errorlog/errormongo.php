<?php
require_once('mongolib.php');

function storeErrorInMongo ($ary)
{
    $ary["insertdate"] = new MongoDate(time());
    if (isset($ary["errordate"])) {
        $dtime = (float)$ary["errordate"];
        $ary["errordate"] = new MongoDate($dtime);
    } else {
        $ary["errordate"] = new MongoDate(time());
    }
    $db = new MongoAnalyticsDB('rawlog');
    $db->save($ary);
    return true;
}
