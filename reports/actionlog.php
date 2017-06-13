<?php
require_once('cron/mongolib.php');
require_once('cron/ipresolver.php');
require_once('cron/bootstrap.php');

@ignore_user_abort(true);
ob_start();

$ary = (array($_GET + $_POST));
$ary["timestamp"] = time();

// When the 'url' and referrer url parameter are not given, we might be in the 'Simple Image Tracker' mode.
// The URL can default to the Referrer, which will be in this case
// the URL of the page containing the Simple Image beacon
if (empty($ary['urlref'])
    && empty($ary['url'])
    && array_key_exists('HTTP_REFERER', $_SERVER)
)
{
    $url = $_SERVER['HTTP_REFERER'];
    if (!empty($url)) {
        $ary['url'] = $url;
    }
}

// check for 4byte utf8 characters in url and replace them with ?
// @TODO Remove as soon as our database tables use utf8mb4 instead of utf8
if (array_key_exists('url', $ary) && preg_match('/[\x{10000}-\x{10FFFF}]/u', $ary['url'])) {
    $ary['url'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $ary['url']);
}

$ary['location_ip'] = getIp();


unset($ary['rec']);  // rec simply means to record;
if (isset($_COOKIE['piwik_ignore'])) {
    echo "Ignored Cookie Detected";
    return;
}

// Check if we exclude IP
// Check if we exclude user agents
// Core/Tracker/VisitExcluded
// to do human bot detection
// To detect spam host

$visitorId = getConfigId ($ary);
$ary['visitorid'] = $visitorId;

$isKnown = findKnownVisitor($visitorId, $ary);
$ary['isVisitorKnown'] = $isKnown;

$isNewVisit  = isVisitNew($ary);
$ary['isNewVisit'] = $isNewVisit;

$actions    = getActionsToLookup();





