<?php
/**
 * ::: PLUGIN MVC ROUTER ::
 * Contains "router" functionality for hooking up controllers with actions in WordPress
 *
 * @Author: Edentic I/S
 * @Version: 1.0
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Edentic I/S

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
 */

namespace WPMVC\Core;

use WPMVC\Core\model\CustomPostModel;

class WPMvcRouter extends WPPluginMVC {

    protected $registerPostTypeModels = array();

    /**
     * Runs the hookup method
     */
    public function __construct() {
        parent::__construct();
        $this->initPostTypeModels();
        $this->hookUp();
    }

    /**
     * Main method for hooking up actions and filters to plugin
     */
    protected function hookUp() {

    }

    /**
     * Initialzes posttype models
     */
    private function initPostTypeModels() {
        foreach($this->registerPostTypeModels as $postTypeModel) {
            /* @var CustomPostModel $postTypeModel */
            $postTypeModel = $this->getFullNamespace($postTypeModel, 'model');
            add_action('init', array($postTypeModel, 'createCustomPostType'));
        }
    }

    /**
     * Returns an array for loading specific controller and method on a specific action or filter
     *
     * @param null $controllerName
     * @param null $method
     * @return array|bool
     * @throws \Exception
     */
    public function loadController($controllerName = null, $method = null) {
        if(!isset($controllerName) || !is_string($controllerName)) return true;
        $controllerName = $this->getFullNamespace($controllerName, 'controller');

        if(!class_exists($controllerName)) {
            if(!$this->registerNamespace($controllerName)) {
                throw new \Exception('Could not find the given controller: '. $controllerName);
            }
        }

        $contollerClass = $this->getClassName($controllerName);
        if(isset($this->$contollerClass)) {
            $controller = $this->$contollerClass;
        } else {
            $this->$contollerClass = new $controllerName;
        }

        $output = array($this->$contollerClass);
        if(isset($method) && is_string($method)) {
            if(method_exists($this->$contollerClass, $method)) {
                $output[] = $method;
            } else {
                throw new \Exception($method. " doesn't exist in ". $controllerName);
            }
        } elseif(method_exists($this->$contollerClass, 'index')) {
            $output[] = "index";
        } else {
            throw new \Exception("Controller doesn't have a index method!");
        }

        return $output;
    }
}