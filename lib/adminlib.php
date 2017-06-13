<?php
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/analytics/locallib.php');

class admin_setting_local_analytics_engine_database_tables_installed extends admin_setting {
    public function __construct($name, $visiblename, $description, $defaultsetting) {
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '', does not write anything
     *
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    public function output_html($data, $query='') {
        if(local_analytics_engine_db_installed()) {
            $formelementcontent = html_writer::span(get_string('engineinstalled', 'local_analytics'), '', array('style' => 'color:#3c763d'));
        } else {
            $formelementcontent = html_writer::span(get_string('enginenotinstalled', 'local_analytics'), '', array('style' => 'color:#a94442'));
			$formelementcontent .= html_writer::span(html_writer::link(new moodle_url('/local/analytics/install.php'), get_string('engineinstall', 'local_analytics')), '', array('style' => 'margin-left:10px'));
        }
        
        return format_admin_setting($this, $this->visiblename, $formelementcontent);
    }
}
