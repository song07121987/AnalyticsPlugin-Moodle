<?php

use Piwik\Config;

/**
 * Main class file
 *
 * @package kDebug
 * @author Xavi Torello <info@xaviertorello.cat>
 */


define ('KDEBUG_LANG_DEFAULT', 'en');

define ('KDEBUG_VERSION', '0.1.1');

define ('KDEBUG_BUILD', '20140908');

define ('KDEBUG_URL', 'http://xaviertorello.cat');

define('KDEBUG_VIEW_DIR_DEFAULT', 'View/');

define('KDEBUG_FORMAT_DEFAULT', 'Html');

define('KDEBUG_BASE_DIR', dirname(__FILE__));



class kDebug
{
    var $language = KDEBUG_LANG_DEFAULT;

    var $_base_dir = KDEBUG_BASE_DIR;

    var $_view_directory = KDEBUG_VIEW_DIR_DEFAULT;

    var $_format = KDEBUG_FORMAT_DEFAULT;

    function kDebug($opts = null)
    {

        $this->_base_dir = dirname(__FILE__);

        if ($opts) {
            if (isset($opts['view_directory'])) {
                $this->setViewDirectory($opts['view_directory']);
            } else {
                $this->setViewDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . KDEBUG_VIEW_DIR_DEFAULT);
            }

            if (isset($opts['format'])) {
                $this->setFormat($opts['format']);
            } else {
                if (!strcasecmp(PHP_SAPI, 'cli')) {
                    $this->setFormat('Cli');
                } else {
                    $this->setFormat(KDEBUG_FORMAT_DEFAULT);
                }
            }

        } else { /* Use defaults */
            $this->setViewDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . KDEBUG_VIEW_DIR_DEFAULT);
            if (!strcasecmp(PHP_SAPI, 'cli')) {
                $this->setFormat('Cli');
            } else {
                $this->setFormat(KDEBUG_FORMAT_DEFAULT);
            }
        }
    }



    function renderOutput($page_title = "Security Information About PHP")
    {
        $this->loadView($this->_format);
    }



    function toogleDebug(){
	$this->kDebugStatusNOU=($this->kDebugStatus)?0:1;

	Config::getInstance()->Tracker['debug'] = $this->kDebugStatusNOU;
	Config::getInstance()->forceSave();
    }



    function loadAndRun()
    {

	$this->kDebugStatus = Config::getInstance()->Tracker['debug'];
	//$this->kToogle=(isset($_GET["kDEBUG"]))?true:false;
	(isset($_GET["kDEBUG"]))?$this->toogleDebug():false;
	$this->kDebugStatus = Config::getInstance()->Tracker['debug'];
    }


    function getResultsAsArray()
    {

	//$configg=$_SERVER["DOCUMENT_ROOT"]."/config/config.ini.php";
	//$results['config']=$configg;

	$results['missatge']="TOOGLE? " . $this->kDebugStatus . " -- " .  $this->kDebugStatus;

	$results['actiu'] = Config::getInstance()->Tracker['debug'];

        return $results;
    }



    function getOutput()
    {
        ob_start();
        $this->renderOutput();
        $output = ob_get_clean();
        return $output . "LOLAZO";
    }



    function loadView($view_name, $data = null)
    {
        if ($data != null) {
            extract($data);
        }

        $view_file = $this->getViewDirectory() . $view_name . ".php";

        if (file_exists($view_file) && is_readable($view_file)) {
            ob_start();
            include $view_file;
            echo ob_get_clean();
        } else {
            user_error("The view '{$view_file}' either does not exist or is not readable", E_USER_WARNING);
        }


    }



    function getViewDirectory()
    {
        return $this->_view_directory;
    }



    function setViewDirectory($newdir)
    {
        $this->_view_directory = $newdir;
    }



    function getFormat()
    {
        return $this->_format;
    }



    function setFormat($format)
    {
        $this->_format = $format;
    }
}


function kDebug()
{
    // modded this to not throw a PHP5 STRICT notice, although I don't like passing by value here
    $kdg = new kDebug();
    $kdg->loadAndRun();
    $kdg->getOutput();
}

