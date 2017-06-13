<?php
function storeErrorsInDB ($arys) {
    foreach ($arys as $err) {
        storeErrorInDB($err);
    }
}
function storeErrorInDB ($ary) {
    global $DB;
    $ary["insertdate"] = date('Y-m-d H:i:s', time());
    if (!isset($ary["errordate"])) {
        $ary["errordate"] = $ary["insertdate"];
    } else {
        $dtime = $ary["errordate"];
        $ary["errordate"] = date('Y-m-d H:i:s', $dtime);
    }
    if (!isset ($ary["manufacturer"]))
        $ary["manufacturer"] = '';
    if (!isset ($ary["device"]))
        $ary["device"] = '';
    if (!isset ($ary["resolution"]))
        $ary["resolution"] = '';
    if (!isset ($ary["orientation"]))
        $ary["orientation"] = '';
    if (!isset ($ary["deviceid"]))
        $ary["deviceid"] = '';
    if (!isset ($ary["userid"]))
        $ary["userid"] = 0;
    if (!isset ($ary["nonfatal"]))
        $ary["nonfatal"] = 1;
    if (!isset ($ary["runtime"]))
        $ary["runtime"] = 0;
    if (!isset ($ary["useragent"]))
        $ary["useragent"] = $_SERVER['HTTP_USER_AGENT'];
    if (!isset ($ary["url"]))
        $ary["url"] = current_url();
    if (!isset ($ary["os"]))
        $ary["os"] = getOS();

    $error = (object) $ary;
	
    $DB->insert_record('analytic_errorlog', $error);
    return true;
    // INSERT INTO {analytic_errorlog} (appname,version,os,osversion,manufacturer,device,resolution,orientation,online,diskspace,deviceid,userid,errordate,description,stack,nonfatal,runtime,ip,insertdate) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
}

function getOS() {
    $user_agent     =   $_SERVER['HTTP_USER_AGENT'];
    $os_platform    =   "Unknown OS Platform";
    $os_array       =   array(
        '/windows nt 10/i'     =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }
    }
    return $os_platform;
}

function current_url() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $proto = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
    } else if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $proto = 'https';
    } else {
        $proto = 'http';
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } else if (!empty($_SERVER['HTTP_HOST'])) {
        $parts = explode(':', $_SERVER['HTTP_HOST']);
        $host = $parts[0];
    } else if (!empty($_SERVER['SERVER_NAME'])) {
        $host = $_SERVER['SERVER_NAME'];
    } else {
        $host = 'unknown';
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
        $port = $_SERVER['HTTP_X_FORWARDED_PORT'];
    } else if (!empty($_SERVER['SERVER_PORT'])) {
        $port = $_SERVER['SERVER_PORT'];
    } else if ($proto === 'https') {
        $port = 443;
    } else {
        $port = 80;
    }

    $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $url = $proto . '://' . $host;
    if (($proto == 'https' && $port != 443) || ($proto == 'http' && $port != 80)) {
        $url .= ':' . $port;
    }
    $url .= $path;
    return $url;
}

