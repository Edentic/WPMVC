<?php
/*
Plugin Name: WPMVC
Plugin URI: http://edentic.dk/
Description: Core of WPMVC framework - has to be enabled for plugin using this framework to work!
Version: 1.0
Author: Edentic I/S
Author URI: http://edentic.dk
*/

namespace WPMVC;
include_once plugin_dir_url(__FILE__). 'Core/splloader.php';

if(!class_exists('SplClassLoader')) {
    throw new \Exception('SplClassLoader cannot be found!');
    die();
}

$splLoader = new \SplClassLoader('WPMVC', WP_PLUGIN_DIR);
$splLoader->register();