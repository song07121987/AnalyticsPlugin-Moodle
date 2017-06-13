<?php
namespace local_analytics\api;

use local_analytics\dimensions;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/analytics/locallib.php');

class piwik extends analytics {
    public static function insert_tracking() {
		global $CFG, $COURSE, $USER;
		
		if(! self::should_track()) {
            return;
        }
		
		if(! isset($CFG->additionalhtmlfooter)) {
            $CFG->additionalhtmlfooter = '';
        }
        $CFG->additionalhtmlfooter = $CFG->additionalhtmlfooter . "
		<script type=\"text/javascript\">
		  var _paq = _paq || [];
			_paq.push(['setCustomVariable', 1, 'Course', ".$COURSE->id.", 'page']);
			_paq.push(['setCustomVariable', 1, 'AccessType', 'Web', 'visit']);
			_paq.push(['trackPageView']);
			_paq.push(['enableLinkTracking']);
		  (function() {
			var u='{$CFG->wwwroot}/local/analytics/engine/';
			_paq.push(['setTrackerUrl', u+'piwik.php']);
			_paq.push(['setSiteId', 1]);
			_paq.push(['setUserId', ".$USER->id."]);
			_paq.push(['enableHeartBeatTimer', 30]);

			var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
			g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
		  })();
		</script>";
    }
	
	public static function should_track() {
        return parent::should_track() and local_analytics_engine_db_installed();
    }
}