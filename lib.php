<?php

use local_analytics\injector;

require_once(__DIR__.'/../../config.php');

function local_analytics_extend_navigation() {
    injector::inject();
}

function local_analytics_extend_settings_navigation() {
    injector::inject();
}

function local_analytics_extend_navigation_user_settings() {
    injector::inject();
}

function local_analytics_extend_navigation_frontpage() {
    injector::inject();
}

function local_analytics_extend_navigation_user() {
    injector::inject();
}

/*function tool_callbacktest_before_http_headers() {
    injector::inject();
}*/