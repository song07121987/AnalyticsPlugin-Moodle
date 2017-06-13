<?php
namespace local_analytics\api;

defined('MOODLE_INTERNAL') || die();

use core\session\manager;

/**
 * Abstract local analytics class.
 */
abstract class analytics {
    /**
     * Encode a substring if required.
     *
     * @param string  $input  The string that might be encoded.
     * @param boolean $encode Whether to encode the URL.
     * @return string
     */
    private static function might_encode($input, $encode) {
        if(! $encode) {
            return str_replace("'", "\'", $input);
        }

        return urlencode($input);
    }

    /**
     * Get the Tracking URL for the request.
     *
     * @param bool|int $urlencode    Whether to encode URLs.
     * @param bool|int $leadingslash Whether to add a leading slash to the URL.
     * @return string A URL to use for tracking.
     */
    public static function trackurl($urlencode = false, $leadingslash = false) {
        global $DB, $PAGE;
        $pageinfo = get_context_info_array($PAGE->context->id);
        $trackurl = "";

        if($leadingslash) {
            $trackurl .= "/";
        }

        // Adds course category name.
        if(isset($pageinfo[1]->category)) {
            if($category = $DB->get_record('course_categories', ['id' => $pageinfo[1]->category])) {
                $cats = explode("/", $category->path);
                foreach(array_filter($cats) as $cat) {
                    if($categorydepth = $DB->get_record("course_categories", ["id" => $cat])) {
                        $trackurl .= self::might_encode($categorydepth->name, $urlencode).'/';
                    }
                }
            }
        }

        // Adds course full name.
        if(isset($pageinfo[1]->fullname)) {
            if(isset($pageinfo[2]->name)) {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode).'/';
            } else {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode);
                $trackurl .= '/';
                if ($PAGE->user_is_editing()) {
                    $trackurl .= get_string('edit', 'local_analytics');
                } else {
                    $trackurl .= get_string('view', 'local_analytics');
                }
            }
        }

        // Adds activity name.
        if(isset($pageinfo[2]->name)) {
            $trackurl .= self::might_encode($pageinfo[2]->modname, $urlencode);
            $trackurl .= '/';
            $trackurl .= self::might_encode($pageinfo[2]->name, $urlencode);
        }

        return $trackurl;
    }

    public static function should_track() {
        return ! is_siteadmin();
    }

    public static function user_full_name() {
        global $USER;
		
        $user = $USER;

        if(manager::is_loggedinas()) {
            $user = manager::get_realuser();
        }

        $realname = fullname($user);
        return $realname;
    }
}
