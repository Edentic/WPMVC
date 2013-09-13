<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Baagoe
 * Date: 20/07/13
 * Time: 15.14
 * To change this template use File | Settings | File Templates.
 */

namespace WPMVC\Core;

class WPMvcRouter extends WPPluginMVC {

    public function __construct() {
        $this->hookUp();
        parent::__construct();
    }

    /**
     * Main method for hooking up actions and filters to plugin
     */
    protected function hookUp() {

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
        $controllerName = $this->getTopNamespace(). "\\controller\\". $controllerName;

        if(!class_exists($controllerName)) {
            throw new \Exception('Could not find the given controller!');
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