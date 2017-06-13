<?php

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function getIp()
{
    $clientHeaders = array();
    $default = '0.0.0.0';
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $default = $_SERVER['REMOTE_ADDR'];
    }

    $ipString = getNonProxyIpFromHeader($default, $clientHeaders);
    return IPUtils::sanitizeIp($ipString);
}

function getNonProxyIpFromHeader($default, $proxyHeaders)
{
    $proxyIps = array();
    /*
    if (isset($config['proxy_ips'])) {
        $proxyIps = $config['proxy_ips'];
    }
    */
    if (!is_array($proxyIps)) {
        $proxyIps = array();
    }

    $proxyIps[] = $default;

    // examine proxy headers
    foreach ($proxyHeaders as $proxyHeader) {
        if (!empty($_SERVER[$proxyHeader])) {
            // this may be buggy if someone has proxy IPs and proxy host headers configured as
            // `$_SERVER[$proxyHeader]` could be eg $_SERVER['HTTP_X_FORWARDED_HOST'] and
            // include an actual host name, not an IP
            $proxyIp = self::getLastIpFromList($_SERVER[$proxyHeader], $proxyIps);
            if (strlen($proxyIp) && stripos($proxyIp, 'unknown') === false) {
                return $proxyIp;
            }
        }
    }

    return $default;
}

function getLastIpFromList($csv, $excludedIps = null)
{
    $p = strrpos($csv, ',');
    if ($p !== false) {
        $elements = explode(',', $csv);
        for ($i = count($elements); $i--;) {
            $element = trim(Common::sanitizeInputValue($elements[$i]));
            $ip = fromStringIP(sanitizeIp($element));
            // if (empty($excludedIps) || (!in_array($element, $excludedIps) && !$ip->isInRanges($excludedIps))) {
            return $element;
            // }
        }
        return '';
    }
    return trim(Common::sanitizeInputValue($csv));
}



function sanitizeIp($ipString)
{
    $ipString = trim($ipString);

    // CIDR notation, A.B.C.D/E
    $posSlash = strrpos($ipString, '/');
    if ($posSlash !== false) {
        $ipString = substr($ipString, 0, $posSlash);
    }

    $posColon = strrpos($ipString, ':');
    $posDot = strrpos($ipString, '.');
    if ($posColon !== false) {
        // IPv6 address with port, [A:B:C:D:E:F:G:H]:EEEE
        $posRBrac = strrpos($ipString, ']');
        if ($posRBrac !== false && $ipString[0] == '[') {
            $ipString = substr($ipString, 1, $posRBrac - 1);
        }

        if ($posDot !== false) {
            // IPv4 address with port, A.B.C.D:EEEE
            if ($posColon > $posDot) {
                $ipString = substr($ipString, 0, $posColon);
            }
            // else: Dotted quad IPv6 address, A:B:C:D:E:F:G.H.I.J
        } else if (strpos($ipString, ':') === $posColon) {
            $ipString = substr($ipString, 0, $posColon);
        }
        // else: IPv6 address, A:B:C:D:E:F:G:H
    }
    // else: IPv4 address, A.B.C.D

    return $ipString;
}

function fromStringIP($ip)
{
    return fromBinaryIP(stringToBinaryIP($ip));
}

function stringToBinaryIP($ipString)
{
    // use @inet_pton() because it throws an exception and E_WARNING on invalid input
    $ip = @inet_pton($ipString);
    return $ip === false ? "\x00\x00\x00\x00" : $ip;
}

function fromBinaryIP($ip)
{
    if ($ip === null || $ip === '') {
        return new IPv4("\x00\x00\x00\x00");
    }

    if (isIPv4($ip)) {
        return new IPv4($ip);
    }

    return new IPv6($ip);
}

function isIPv4($binaryIp)
{
    // in case mbstring overloads strlen function
    $strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';

    return $strlen($binaryIp) == 4;
}
