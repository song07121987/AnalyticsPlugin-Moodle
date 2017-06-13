<?php
namespace Piwik\Config;

class ClientConfig
{
    const DEFAULT_CLIENT = 'MQLP';
    const PIWIK_URL = "http://localhost/mqlp/analytic/api/";
    const DISABLE_UPDATE = true;
    const GLOBAL_CACHE = 'Eager';
    const isBrowserTriggerEnabled = false;

    public static function isBranded()
    {
        return true;
    }

    public static function disableNonceCheck () {
        return true;
    }

    public static function getTmpPath () {
        return false;
        // return 'C:\work\mqlp\working\moodledata\analytics';
    }

    public static function isOptimised () {
        return true;
    }
}