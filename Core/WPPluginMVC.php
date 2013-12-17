<?php
/**
 * ::: PLUGIN MVC CORE ::
 *  WPPluginMVC class is the core of the framework, and contains core methods for register namespaces, loading in views and models.
 *  Every class(controller, Model) is nested from this core and has access to these functions.
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

namespace plugins\WPMVC\Core;

class WPPluginMVC
{
    /**
     * Contains path to view folder
     * @var string
     */
    private  $viewFolder;

    /**
     * Contains path to CSS folder
     * @var string
     */
    private  $cssFolder;

    /**
     * Contains path to JavaScript folder
     * @var string
     */
    private  $jsFolder;

    /**
     * Constructor method for core - setting up folder paths used to get files
     */
    public function __construct() {
        $this->viewFolder = $this->getPluginDir(). "view/";
        $this->cssFolder = $this->getPluginDir(). "css/";
        $this->jsFolder = $this->getPluginDir(). "js/";
    }

    /**
     * Load a view from the given file name in view folder
     *
     * @param null $view - Filename of the view without extension
     * @param array $data - Array of variables that should be parsed to view
     * @param bool $return - If true the view will be returned as a string
     * @return string
     * @throws \Exception
     */
    public function loadView($view = null, $data = array(), $return = false) {
        if(!isset($view)) {
            return;
        }

        $view = $this->viewFolder. $view. ".php";

        if(file_exists($view)) {
            if(is_array($data) && count($data) > 0) {
                extract($data);
            }
            if($return) {
                ob_start();
            }

            include $view;

            if($return) {
                return ob_get_clean();
            }

        } else {
            throw new \Exception($view. " doesn't exists!");
        }
    }

    /**
     * Loads a new model into context
     * The model can be retrived through $this-><modelname>
     *
     * @param $modelname - Class name of model
     * @throws \Exception
     * @throws Exception
     */
    public function loadModel($modelname) {
        $class = $this->getClassName($modelname);
        if(isset($this->$class)) return;
        if(!(isset($modelname) && is_string($modelname))) throw new Exception('Given parameter is not a string!');
        $modelname = $this->getModelFullNamespace($modelname, 'model');

        if(class_exists($modelname)) {
            $this->$class = new $modelname();
        } else {
            if(!$this->registerNamespace($modelname)) {
                throw new \Exception($modelname. " does not exist!");
            }
        }
    }

    /**
     * Returns full path of a class
     * @param $name
     * @param $type
     * @internal param string $modelname
     * @return string
     */
    public function getFullNamespace($name, $type) {
        return $this->getTopNamespace(). "\\". $type. "\\". $name;
    }

    /**
     * Returns link to admin page
     *
     * @param $link - Name of the page
     * @param array $parms - List of get parameters attached to link
     * @return string
     */
    public function getLink($link, $parms = array()) {
        if(!is_string($link)) return $link;

        $link = "admin.php?page=". $link;
        if(is_array($parms) && count($parms) > 0) {
            foreach($parms as $key => $value) {
                $link .= "&". $key. "=". $value;
            }
        }

        return get_admin_url(null, $link);
    }

    /**
     * Returns class name of given path to file
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function getClassName($path) {
        if(!is_string($path)) throw new Exception('Path given is not a string!');
        $parts = explode("\\", $path);
        if(count($parts) > 0) {
            $ret = $parts[count($parts) - 1];
        } else {
            $ret = $parts;
        }

        return str_replace(".php", "", $ret);
    }

    /**
     * Returns toplevel namespace of current class
     *
     * @return string
     * @throws \Exception
     */
    protected function getTopNamespace() {
        $namespace = get_class($this);
        $levels = explode('\\', $namespace);

        if(is_array($levels)) {
            return $levels[0]. '\\'. $levels[1];
        } elseif(is_string($levels)) {
            return $levels;
        }

        throw new \Exception('Toplevel could not be found!');
    }

    /**
     * Returns path to plugin directory of current plugin
     * @return string
     */
    public function getPluginDir() {
        $pluginName = $this->getTopNamespace();
        $path = WP_PLUGIN_DIR. "/". $pluginName. "/";
        return $path;
    }

    /**
     * Returns path to CSS folder, or CSS file if specified
     * @param string $file
     * @return string
     */
    public function getCssFolder($file = "")
    {
        if(strlen($file) > 0) {
            return $this->cssFolder. $file;
        }

        return $this->cssFolder;
    }

    /**
     * Returns path to JS folder, or file if specified
     * @param string $file
     * @return string
     */
    public function getJsFolder($file = "")
    {
        if(strlen($file) > 0) {
            return $this->jsFolder. $file;
        }
        return $this->jsFolder;
    }

    /**
     * Registers namespace for given class name
     * @param $class
     * @return bool
     */
    protected function registerNamespace($class) {
        if(!class_exists($class)) {
            $splLoader = new \SplClassLoader($this->getTopNamespace(), WP_PLUGIN_DIR);
            $splLoader->register();
        }

        return class_exists($class);
    }
}
