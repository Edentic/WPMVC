<?php
/**
 * ::: PLUGIN CONTROLLER ::
 * Contains standard functions for loading in plugins etc.
 *
 * @Author: Edentic I/S
 * @Version: 1.0
 */
namespace WPMVC\Core;

class WPPluginMVC
{
    private  $viewFolder;
    private  $cssFolder;
    private  $jsFolder;

    public function __construct() {
        $this->viewFolder = $this->getPluginDir(). "view/";
        $this->cssFolder = $this->getPluginDir(). "css/";
        $this->jsFolder = $this->getPluginDir(). "js/";
    }

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
     * @param $modelname
     * @throws \Exception
     * @throws Exception
     */
    public function loadModel($modelname) {
        $class = $this->getClassName($modelname);
        if(isset($this->$class)) return;
        if(!(isset($modelname) && is_string($modelname))) throw new Exception('Given parameter is not a string!');
        $modelname = $this->getTopNamespace(). "\\model\\". $modelname;

        if(class_exists($modelname)) {
            $this->$class = new $modelname();
        } else {
            if(!$this->registerNamespace($modelname)) {
                throw new \Exception($modelname. " does not exist!");
            }
        }
    }

    /**
     * Returns link to admin page
     *
     * @param $link
     * @param array $parms
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
     * Returns filename for a path
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
     * Returns toplevel namespace
     * @return string
     * @throws \Exception
     */
    protected function getTopNamespace() {
        $namespace = get_class($this);
        $levels = explode('\\', $namespace);

        if(is_array($levels)) {
            return $levels[0];
        } elseif(is_string($levels)) {
            return $levels;
        }

        throw new \Exception('Toplevel could not be found!');
    }

    /**
     * Returns path to plugin directory
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
     * Registers namespace for plugin
     */
    protected function registerNamespace($class) {
        if(!class_exists($class)) {
            $splLoader = new \SplClassLoader($this->getTopNamespace(), WP_PLUGIN_DIR);
            $splLoader->register();
        }

        return class_exists($class);
    }
}
