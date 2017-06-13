1. Schema files

The SQL schema files are contained in the 'schema' dir of the plugin.
They are encoded in UTF-8 and all such schema files should be in UTF-8 or they cannot be parsed in PHP correctly.
The 'DROP TABLE' statements are omitted while parsing the sql file as such statements cause an error if a table doesn't exist.
We need here something like 'IF EXISTS' as in MySQL.
And on the contrary, if tables exist already the installation will fail because the error like 'the table exists already' will arise.
So, the tracking engine tables should be installed when no tables from schema file present in the system at the moment, otherwise it may cause SQL errors.

The 'DROP CONSTRAINT' statements are also omitted as they cause errors on my setup, couldn't figure it out.

2. Error logging

Couldn't get this work properly, saving an error in database fails. It causes dml exception on my setup and I wasn't able to find the source of it.

3. Analytics reports

Reports scripts contain SQL queries which refer to some custom fields and tables used on your customized setup so I wasn't able to test reports properly as it gives
SQL errors. At least reports do not generate fatal errors when require some missing php files or whatever.

4. Error handling
There is no way to read the local plugin config in default_error_handler() and default_exception_handler() functions in setuplib.php in Moodle.
That's why it's impossible to replace the 'isset($CFG->mq_errorlog) && $CFG->mq_errorlog == 1' statement with config values from the local plugin.

Here are modified setuplib.php functions with corrected require paths and omitted '$CFG->mq_errorlog' references:

function default_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    // Here we add the error loggin capabilities
    global $CFG;
	
    if (file_exists($CFG->dirroot . '/local/analytics/errorlog/mq_errorlog.php')) {
        require_once($CFG->dirroot . '/local/analytics/errorlog/mq_errorlog.php');
        MQErrorLogger::init();
        MQErrorLogger::report_php_error($errno, $errstr, $errfile, $errline);
    }
    // end of error logging
    if ($errno == 4096) {
        //fatal catchable error
        throw new coding_exception('PHP catchable fatal error', $errstr);
    }
    return false;
}

function default_exception_handler($ex) {
    global $CFG, $DB, $OUTPUT, $USER, $FULLME, $SESSION, $PAGE;
	
    // Here we add error loggin capabilities
    if (file_exists($CFG->dirroot . '/local/analytics/errorlog/mq_errorlog.php')) {
        require_once($CFG->dirroot . '/local/analytics/errorlog/mq_errorlog.php');
        MQErrorLogger::init();
        MQErrorLogger::report_exception($ex);
    }
    // end of error loggin

    // detect active db transactions, rollback and log as error
    abort_all_db_transactions();
}