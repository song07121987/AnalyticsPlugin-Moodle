<?php
namespace local_analytics;

defined('MOODLE_INTERNAL') || die();

class injector {
	
    private static $injected = false;

    public static function inject() {
        if(self::$injected) {
            return;
        }
		
        self::$injected = true;

        if(get_config('local_analytics', 'enabletracking')) {
			\local_analytics\api\piwik::insert_tracking();
		}
    }

    public static function reset() {
        self::$injected = false;
    }
}