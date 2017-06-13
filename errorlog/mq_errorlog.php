<?php
include 'mq_errorlevel.php';

if ( !defined( 'BASE_EXCEPTION' ) ) {
    define( 'BASE_EXCEPTION', version_compare( phpversion(), '7.0', '<' )? '\Exception': '\Throwable' );
}

class MQErrorLogger {
    /** @var MQErrorNotifier */
    public static $instance = null;

    public static function init($config = array(), $set_exception_handler = true, $set_error_handler = true, $report_fatal_errors = true) {
        if (self::$instance == null) {
            self::$instance = new MQErrorNotifier($config);
            /*
            if ($set_exception_handler) {
                set_exception_handler('MQErrorLogger::report_exception');
            }
            if ($set_error_handler) {
                set_error_handler('MQErrorLogger::report_php_error');
            }
            */
            if ($report_fatal_errors) {
                register_shutdown_function('MQErrorLogger::report_fatal_error');
            }
            if (self::$instance->batched) {
                register_shutdown_function('MQErrorLogger::flush');
            }
        }
    }

    public static function report_exception($exc, $extra_data = null, $payload_data = null) {
        if (self::$instance == null) {
            return;
        }
        self::setUser();
        return self::$instance->report_exception($exc, $extra_data, $payload_data);
    }

    public static function setUser () {
        global $USER;
        if (isset ($USER) && !isset(self::$instance->userid)) {
            self::$instance->userid = $USER->id;
            self::$instance->logintime = $USER->currentlogin;
        }
    }

    public static function report_message($message, $level = LogLevel::ERROR, $extra_data = null, $payload_data = null) {
        if (self::$instance == null) {
            return;
        }
        self::setUser();
        return self::$instance->report_message($message, $level, $extra_data, $payload_data);
    }

    public static function report_fatal_error() {
        // Catch any fatal errors that are causing the shutdown
        $last_error = error_get_last();
        if (!is_null($last_error)) {
            switch ($last_error['type']) {
                case E_PARSE:
                case E_ERROR:
                    self::setUser();
                    self::$instance->report_php_error($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
                    break;
            }
        }
    }

    // This function must return false so that the default php error handler runs
    public static function report_php_error($errno, $errstr, $errfile, $errline) {
        if (self::$instance != null) {
            self::setUser();
            self::$instance->report_php_error($errno, $errstr, $errfile, $errline);
        }
        return false;
    }

    public static function flush() {
        self::$instance->flush();
    }
}

// Send errors that have these levels
if (!defined('MQERRORLOG_INCLUDED_ERRNO_BITMASK')) {
    define('MQERRORLOG_INCLUDED_ERRNO_BITMASK', E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_NOTICE);
}

class MQErrorNotifier {
    // optional / defaults
    public $base_api_url;
    public $batch_size = 50;
    public $batched = true;
    public $capture_error_backtraces = true;
    public $environment = array();
    public $error_sample_rates = array();

    // available handlers: blocking, agent
    public $host = null;
    public $handler = 'direct';
    public $included_errno = MQERRORLOG_INCLUDED_ERRNO_BITMASK;
    public $userid = null;
    public $logintime = 0;
    public $scrub_fields = array('passwd', 'pass', 'password', 'secret', 'confirm_password',
        'password_confirmation', 'auth_token', 'csrf_token');
    public $shift_function = true;
    public $timeout = 3;
    public $report_suppressed = false;
    public $use_error_reporting = false;

    public $proxy = null;
    public $include_error_code_context = false;
    public $include_exception_code_context = false;

    private $config_keys = array('base_api_url', 'batch_size', 'batched',
        'capture_error_backtraces', 'environment', 'error_sample_rates',
        'host', 'included_errno', 'userid',
        'scrub_fields', 'shift_function', 'timeout', 'report_suppressed', 'use_error_reporting', 'proxy',
        'include_error_code_context', 'include_exception_code_context');

    // cached values for request/server/person data
    private $_php_context = null;

    // payload queue, used when $batched is true
    private $_queue = array();

    private $_iconv_available = null;

    private $_mt_randmax;

    private $_curl_ipresolve_supported;

    /** @var iSourceFileReader $_source_file_reader */
    private $_source_file_reader;

    public function __construct($config) {
        global $CFG;
		
		$this->base_api_url = $CFG->wwwroot.'/local/analytics/errorlog.php';
		
        if (isset ($CFG->mq_errorlogmode)) {
            $this->handler = $CFG->mq_errorlogmode;
        }
        if ($this->handler == 'url') {
            $this->base_api_url = $CFG->wwwroot . '/local/analytics/errorlog.php';
            if (isset ($CFG->mq_errorlogurl)) {
                $this->base_api_url = $CFG->mq_errorlogurl;
            }
        }
        foreach ($this->config_keys as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
            }
        }
        $this->_source_file_reader = new SourceFileReader();

        // fill in missing values in error_sample_rates
        $levels = array(E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING,
            E_USER_NOTICE, E_STRICT, E_RECOVERABLE_ERROR);

        // PHP 5.3.0
        if (defined('E_DEPRECATED')) {
            $levels = array_merge($levels, array(E_DEPRECATED, E_USER_DEPRECATED));
        }

        // PHP 5.3.0
        $this->_curl_ipresolve_supported = defined('CURLOPT_IPRESOLVE');

        $curr = 1;
        for ($i = 0, $num = count($levels); $i < $num; $i++) {
            $level = $levels[$i];
            if (isset($this->error_sample_rates[$level])) {
                $curr = $this->error_sample_rates[$level];
            } else {
                $this->error_sample_rates[$level] = $curr;
            }
        }

        // cache this value
        $this->_mt_randmax = mt_getrandmax();
    }

    public function report_exception($exc, $extra_data = null, $payload_data = null, $nonfatal = 1) {
        try {
            if ( !is_a( $exc, BASE_EXCEPTION ) ) {
                throw new Exception(sprintf('Report exception requires an instance of %s.', BASE_EXCEPTION ));
            }

            return $this->_report_exception($exc, $extra_data, $payload_data, $nonfatal);
        } catch (Exception $e) {
            try {
                // $this->log_error("Exception while reporting exception");
            } catch (Exception $e) {
                // swallow
            }
        }
    }

    public function report_message($message, $level = LogLevel::ERROR, $extra_data = null, $payload_data = null) {
        try {
            return $this->_report_message($message, $level, $extra_data, $payload_data);
        } catch (Exception $e) {
            try {
                // $this->log_error("Exception while reporting message");
            } catch (Exception $e) {
                // swallow
            }
        }
    }

    public function report_php_error($errno, $errstr, $errfile, $errline) {
        try {
            return $this->_report_php_error($errno, $errstr, $errfile, $errline);
        } catch (Exception $e) {
            try {
                // $this->log_error("Exception while reporting php error");
            } catch (Exception $e) {
                // swallow
            }
        }
    }

    /**
     * Flushes the queue.
     * Called internally when the queue exceeds $batch_size, and by MQErrorLogger::flush
     * on shutdown.
     */
    public function flush() {
        $queue_size = $this->queueSize();
        if ($queue_size > 0) {
            $this->send_batch($this->_queue);
            $this->_queue = array();
        }
    }

    /**
     * Returns the current queue size.
     */
    public function queueSize() {
        return count($this->_queue);
    }

    protected function backtrace($exc)
    {
        $backtrace = [];
        $backtrace[] = [
            'file' => $exc->getFile(),
            'line' => $exc->getLine(),
            'function' => '',
        ];
        $trace = $exc->getTrace();
        foreach ($trace as $frame) {
            $func = $frame['function'];
            if (isset($frame['class']) && isset($frame['type'])) {
                $func = $frame['class'].$frame['type'].$func;
            }
            if (count($backtrace) > 0) {
                $backtrace[count($backtrace)-1]['function'] = $func;
            }
            $backtrace[] = [
                'file' => isset($frame['file']) ? $frame['file'] : '',
                'line' => isset($frame['line']) ? $frame['line'] : 0,
                'function' => '',
            ];
        }
        return json_encode($backtrace);
    }

    /**
     * @param \Throwable|\Exception $exc
     */
    protected function _report_exception( $exc, $errordata = null, $payload_data = null, $nonfatal = 1) {
        if (error_reporting() === 0 && !$this->report_suppressed) {
            // ignore
            return;
        }

        $data = $this->build_base_data();
        $data['description'] = $exc->getMessage();
        // $data['stack'] = $this->backtrace($exc);
        $trace_chain = $this->build_exception_trace_chain($exc);
        if (count($trace_chain) > 1) {
            $data['stack'] = json_encode($trace_chain);
        } else {
            $data['stack'] = json_encode($trace_chain[0]);
        }
        $data['nonfatal'] = $nonfatal;
        if ('http' === $this->_php_context) {
            $data['url'] = $this->scrub_url($this->current_url());
            $data['ip'] = $this->user_ip();
        }
        if ($this->userid != null) {
            $data['userid'] = $this->userid;
        }
        if ($this->logintime > 0) {
            $data['runtime'] = time() - $this->logintime;
        }
        $data['environment'] = json_encode($data['environment']);
        $data = $this->_sanitize_keys($data);
        array_walk_recursive($data, array($this, '_sanitize_utf8'));

        $this->send_payload($data);
    }

    protected function _sanitize_utf8(&$value) {
        if (!isset($this->_iconv_available)) {
            $this->_iconv_available = function_exists('iconv');
        }
        if (is_string($value) && $this->_iconv_available) {
            $value = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        }
    }

    protected function _sanitize_keys(array $data) {
        $response = array();
        foreach ($data as $key => $value) {
            $this->_sanitize_utf8($key);
            if (is_array($value)) {
                $response[$key] = $this->_sanitize_keys($value);
            } else {
                $response[$key] = $value;
            }
        }

        return $response;
    }

    protected function _report_php_error($errno, $errstr, $errfile, $errline) {
        if (error_reporting() === 0 && !$this->report_suppressed) {
            // ignore
            return;
        }

        if ($this->use_error_reporting && (error_reporting() & $errno) === 0) {
            // ignore
            return;
        }

        if ($this->included_errno != -1 && ($errno & $this->included_errno) != $errno) {
            // ignore
            return;
        }

        if (isset($this->error_sample_rates[$errno])) {
            // get a float in the range [0, 1)
            // mt_rand() is inclusive, so add 1 to mt_randmax
            $float_rand = mt_rand() / ($this->_mt_randmax + 1);
            if ($float_rand > $this->error_sample_rates[$errno]) {
                // skip
                return;
            }
        }

        $data = $this->build_base_data();

        // set error level and error constant name
        $level = LogLevel::INFO;
        $nonfatal = 1;
        $constant = '#' . $errno;
        switch ($errno) {
            case 1:
                $level = LogLevel::ERROR;
                $constant = 'E_ERROR';
                $nonfatal = 0;
                break;
            case 2:
                $level = LogLevel::WARNING;
                $constant = 'E_WARNING';
                break;
            case 4:
                $level = LogLevel::CRITICAL;
                $constant = 'E_PARSE';
                $nonfatal = 0;
                break;
            case 8:
                $level = LogLevel::INFO;
                $constant = 'E_NOTICE';
                break;
            case 256:
                $level = LogLevel::ERROR;
                $constant = 'E_USER_ERROR';
                $nonfatal = 0;
                break;
            case 512:
                $level = LogLevel::WARNING;
                $constant = 'E_USER_WARNING';
                break;
            case 1024:
                $level = LogLevel::INFO;
                $constant = 'E_USER_NOTICE';
                break;
            case 2048:
                $level = LogLevel::INFO;
                $constant = 'E_STRICT';
                break;
            case 4096:
                $level = LogLevel::ERROR;
                $constant = 'E_RECOVERABLE_ERROR';
                break;
            case 8192:
                $level = LogLevel::INFO;
                $constant = 'E_DEPRECATED';
                break;
            case 16384:
                $level = LogLevel::INFO;
                $constant = 'E_USER_DEPRECATED';
                break;
        }

        // use the whole $errstr. may want to split this by colon for better de-duping.
        $error_class = $constant . ': ' . $errstr;
        $data['description'] = $error_class;
        $data['errorlevel'] = $level;
        $data['nonfatal'] = $nonfatal;

        // build something that looks like an exception
        $data['stack'] = json_encode(array(
            'trace' => array(
                'frames' => $this->build_error_frames($errfile, $errline),
                'exception' => array(
                    'class' => $error_class
                )
            )
        ));

        // request, server, person data
        $data['url'] = $this->scrub_url($this->current_url());
        $data['ip'] = $this->user_ip();
        if ($this->userid != null) {
            $data['userid'] = $this->userid;
        }
        if ($this->logintime > 0) {
            $data['runtime'] = time() - $this->logintime;
        }
        $data['environment'] = json_encode($_SERVER);
        array_walk_recursive($data, array($this, '_sanitize_utf8'));
        $this->send_payload($data);
    }

    protected function _report_message($message, $level, $extra_data, $payload_data) {
        $data = $this->build_base_data();
        $data['description'] = $message;
        $data['errorlevel'] = strtolower($level);
        $data['stack'] = $extra_data;

        $message_obj = array('body' => $message);
        if ($extra_data !== null && is_array($extra_data)) {
            // merge keys from $extra_data to $message_obj
            foreach ($extra_data as $key => $val) {
                if ($key == 'body') {
                    // rename to 'body_' to avoid clobbering
                    $key = 'body_';
                }
                $message_obj[$key] = $val;
            }
            $data['stack'] = $message_obj;
        }

        $data['url'] = $this->scrub_url($this->current_url());
        $data['ip'] = $this->user_ip();
        if ($this->userid != null) {
            $data['userid'] = $this->userid;
        }
        if ($this->logintime > 0) {
            $data['runtime'] = time() - $this->logintime;
        }
        $data['environment'] = json_encode($data['environment']);

        /*
        // merge $payload_data into $data
        // (overriding anything already present)
        if ($payload_data !== null && is_array($payload_data)) {
            foreach ($payload_data as $key => $val) {
                $data[$key] = $val;
            }
        }
        */

        array_walk_recursive($data, array($this, '_sanitize_utf8'));
        $this->send_payload($data);
    }

    protected function scrub_url($url) {
        $url_query = parse_url($url, PHP_URL_QUERY);
        if (!$url_query) return $url;
        parse_str($url_query, $parsed_output);
        // using x since * requires URL-encoding
        $scrubbed_params = $this->scrub_request_params($parsed_output, 'x');
        $scrubbed_url = str_replace($url_query, http_build_query($scrubbed_params), $url);
        return $scrubbed_url;
    }

    protected function scrub_request_params($params, $replacement = '*') {
        $scrubbed = array();
        $potential_regex_filters = array_filter($this->scrub_fields, function($field) {
            return strpos($field, '/') === 0;
        });
        foreach ($params as $k => $v) {
            if ($this->_key_should_be_scrubbed($k, $potential_regex_filters)) {
                $scrubbed[$k] = $this->_scrub($v, $replacement);
            } elseif (is_array($v)) {
                // recursively handle array params
                $scrubbed[$k] = $this->scrub_request_params($v, $replacement);
            } else {
                $scrubbed[$k] = $v;
            }
        }

        return $scrubbed;
    }

    protected function _key_should_be_scrubbed($key, $potential_regex_filters) {
        if (in_array(strtolower($key), $this->scrub_fields, true)) return true;
        foreach ($potential_regex_filters as $potential_regex) {
            if (@preg_match($potential_regex, $key)) return true;
        }
        return false;
    }

    protected function _scrub($value, $replacement = '*') {
        $count = is_array($value) ? count($value) : strlen($value);
        return str_repeat($replacement, $count);
    }

    protected function headers() {
        $headers = array();
        foreach ($this->scrub_request_params($_SERVER) as $key => $val) {
            if (substr($key, 0, 5) == 'HTTP_') {
                // convert HTTP_CONTENT_TYPE to Content-Type, HTTP_HOST to Host, etc.
                $name = strtolower(substr($key, 5));
                if (strpos($name, '_') != -1) {
                    $name = preg_replace('/ /', '-', ucwords(preg_replace('/_/', ' ', $name)));
                } else {
                    $name = ucfirst($name);
                }
                $headers[$name] = $val;
            }
        }

        if (count($headers) > 0) {
            return $headers;
        } else {
            // serializes to emtpy json object
            return new stdClass;
        }
    }

    protected function current_url() {
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

    protected function user_ip() {
        $forwardfor = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
        if ($forwardfor) {
            // return everything until the first comma
            $parts = explode(',', $forwardfor);
            return $parts[0];
        }
        $realip = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : null;
        if ($realip) {
            return $realip;
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * @param \Throwable|\Exception $exc
     * @param mixed $extra_data
     * @return array
     */
    protected function build_exception_trace($exc, $extra_data = null)
    {
        $message = $exc->getMessage();

        $trace = array(
            'frames' => $this->build_exception_frames($exc),
            'exception' => array(
                'class' => get_class($exc),
                'message' => !empty($message) ? $message : 'unknown',
            ),
        );

        if ($extra_data !== null) {
            $trace['extra'] = $extra_data;
        }

        return $trace;
    }

    /**
     * @param \Throwable|\Exception $exc
     * @param array $extra_data
     * @return array
     */
    protected function build_exception_trace_chain( $exc, $extra_data = null)
    {
        $chain = array();
        $chain[] = $this->build_exception_trace($exc, $extra_data);

        $previous = $exc->getPrevious();

        while ( is_a( $previous, BASE_EXCEPTION ) ) {
            $chain[] = $this->build_exception_trace($previous);
            $previous = $previous->getPrevious();
        }

        return $chain;
    }

    /**
     * @param \Throwable|\Exception $exc
     * @return array
     */
    protected function build_exception_frames($exc) {
        $frames = array();

        foreach ($exc->getTrace() as $frame) {
            $framedata = array(
                'filename' => isset($frame['file']) ? $frame['file'] : '<internal>',
                'lineno' =>  isset($frame['line']) ? $frame['line'] : 0,
                'method' => $frame['function']
                // TODO include args? need to sanitize first.
            );
            if($this->include_exception_code_context && isset($frame['file']) && isset($frame['line'])) {
                $this->add_frame_code_context($frame['file'], $frame['line'], $framedata);
            }
            $frames[] = $framedata;
        }

        // MQErrorLogger expects most recent call to be last, not first
        $frames = array_reverse($frames);

        // add top-level file and line to end of the reversed array
        $file = $exc->getFile();
        $line = $exc->getLine();
        $framedata = array(
            'filename' => $file,
            'lineno' => $line
        );
        if($this->include_exception_code_context) {
            $this->add_frame_code_context($file, $line, $framedata);
        }
        $frames[] = $framedata;

        $this->shift_method($frames);

        return $frames;
    }

    protected function shift_method(&$frames) {
        if ($this->shift_function) {
            // shift 'method' values down one frame, so they reflect where the call
            // occurs (like MQErrorLogger expects), instead of what is being called.
            for ($i = count($frames) - 1; $i > 0; $i--) {
                $frames[$i]['method'] = $frames[$i - 1]['method'];
            }
            $frames[0]['method'] = '<main>';
        }
    }

    protected function build_error_frames($errfile, $errline) {
        if ($this->capture_error_backtraces) {
            $frames = array();
            $backtrace = debug_backtrace();
            foreach ($backtrace as $frame) {
                // skip frames in this file
                if (isset($frame['file']) && $frame['file'] == __FILE__) {
                    continue;
                }
                // skip the confusing set_error_handler frame
                if ($frame['function'] == 'report_php_error' && count($frames) == 0) {
                    continue;
                }

                $framedata = array(
                    // Sometimes, file and line are not set. See:
                    // http://stackoverflow.com/questions/4581969/why-is-debug-backtrace-not-including-line-number-sometimes
                    'filename' => isset($frame['file']) ? $frame['file'] : "<internal>",
                    'lineno' =>  isset($frame['line']) ? $frame['line'] : 0,
                    'method' => $frame['function']
                );
                if($this->include_error_code_context && isset($frame['file']) && isset($frame['line'])) {
                    $this->add_frame_code_context($frame['file'], $frame['line'], $framedata);
                }
                $frames[] = $framedata;
            }

            // MQErrorLogger expects most recent call last, not first
            $frames = array_reverse($frames);

            // add top-level file and line to end of the reversed array
            $framedata = array(
                'filename' => $errfile,
                'lineno' => $errline
            );
            if($this->include_error_code_context) {
                $this->add_frame_code_context($errfile, $errline, $framedata);
            }
            $frames[] = $framedata;

            $this->shift_method($frames);

            return $frames;
        } else {
            return array(
                array(
                    'filename' => $errfile,
                    'lineno' => $errline
                )
            );
        }
    }

    protected function getOS() {
        $user_agent     =   isset($_SERVER['HTTP_USER_AGENT']) ?: "Unknown User Agent";
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

    protected function build_base_data($level = LogLevel::ERROR) {
        global $CFG;
        if (null === $this->_php_context) {
            $this->_php_context = $this->get_php_context();
        }

        $data = array(
            'appname' => $CFG->sysname,
            'version' => $CFG->releaseversion,
            'os' => $this->getOS(),
            'online' => 1,
            'diskspace' => (int) (disk_free_space(".") / 1024),
            'environment' => '',
            'errorlevel' => $level,
            'language' => 'php ' . phpversion(),
            'php_context' => $this->_php_context,
            'useragent' => isset($_SERVER['HTTP_USER_AGENT']) ?: "Unknown User Agent"
        );

        return $data;
    }

    protected function send_payload($payload) {
        if ($this->batched) {
            if ($this->queueSize() >= $this->batch_size) {
                // flush queue before adding payload to queue
                $this->flush();
            }
            $this->_queue[] = $payload;
        } else {
            $this->_send_payload($payload);
        }
    }

    /**
     * Sends a single payload to the /item endpoint.
     * $payload - php array
     */
    protected function _send_payload($payload) {
        if ($this->handler === 'direct') {
            require_once ('errordb.php');
            storeErrorInDB ($payload);
        } else {
            $post_data = make_url_data($payload);
            $this->make_api_call('item', $post_data);
        }
    }

    /**
     * Sends a batch of payloads to the /batch endpoint.
     * A batch is just an array of standalone payloads.
     * $batch - php array of payloads
     */
    protected function send_batch($batch) {
        if ($this->handler === 'direct') {
            require_once ('errordb.php');
            storeErrorsInDB ($batch);
        } else {
            $post_data = $this->make_url_data($batch);
            $this->make_api_call('item_batch', $post_data);
        }
    }

    protected function make_url_data ($data) {
        return json_encode ($data);
    }

    protected function get_php_context() {
        return php_sapi_name() === 'cli' || defined('STDIN') ? 'cli' : 'http';
    }

    protected function make_api_call($action, $post_data) {
        $url = $this->base_api_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        if ($this->proxy) {
            $proxy = is_array($this->proxy) ? $this->proxy : array('address' => $this->proxy);

            if (isset($proxy['address'])) {
                curl_setopt($ch, CURLOPT_PROXY, $proxy['address']);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }

            if (isset($proxy['username']) && isset($proxy['password'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'] . ':' . $proxy['password']);
            }
        }

        if ($this->_curl_ipresolve_supported) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        $result = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status_code != 200) {
            // invalid input
        }
    }


    protected function add_frame_code_context($file, $line, array &$framedata) {
        $source = $this->get_source_file_reader()->read_as_array($file);
        if (is_array($source)) {
            $source = str_replace(array("\n", "\t", "\r"), '', $source);
            $total = count($source);
            $line = $line - 1;
            $framedata['code'] = $source[$line];
            $offset = 6;
            $min = max($line - $offset, 0);
            if ($min !== $line) {
                $framedata['context']['pre'] = array_slice($source, $min, $line - $min);
            }
            $max = min($line + $offset, $total);
            if ($max !== $line) {
                $framedata['context']['post'] = array_slice($source, $line + 1, $max - $line);
            }
        }
    }

    protected function get_source_file_reader() { return $this->_source_file_reader; }
}

interface iSourceFileReader {

    /**
     * @param string $file_path
     * @return string[]
     */
    public function read_as_array($file_path);
}

class SourceFileReader implements iSourceFileReader {

    public function read_as_array($file_path) { return file($file_path); }
}