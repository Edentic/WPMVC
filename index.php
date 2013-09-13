<?php
/*
Plugin Name: WPMVC
Plugin URI: http://edentic.dk/
Description: Core of WPMVC framework - has to be enabled for plugin using this framework to work!
Version: 1.0
Author: Edentic I/S
Author URI: http://edentic.dk
*/

namespace SMSGateway;

//Loading in loader
use SMSGateway\app\Init;
use SMSGateway\app\Plugin;

//Setting plugin path
define('SMSGATEWAY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SMSGATEWAY_URL_PATH', plugin_dir_url((__FILE__)));

include_once SMSGATEWAY_PLUGIN_PATH. 'Core/splloader.php';

if(!class_exists('SplClassLoader')) {
    throw new \Exception('SplClassLoader cannot be found!');
    die();
}

$splLoader = new \SplClassLoader('SMSGateway', WP_PLUGIN_DIR);
$splLoader->register();

if(!isset($SMSGateway)) {
    $SMSGateway = new Plugin();
}