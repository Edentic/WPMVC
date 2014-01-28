WPMVC - Intro
=============
WPMVC is a framework for developing WordPress plugins using the normal MVC approach.
It uses a structure where core and plugins using the core is separated, so changes to the core not means updating your plugin or vice versa.

WPMVC is installed as a normal WP plugin, but does not add anything to the user interface. Instead it adds some tools and guidelines for the plugin developer.

The framework uses the PSR-0 standard to autoload files by namespace. The topnamespace for the framework itself is `\plugins` where WPMVC is stored in `\plugins\WPMVC` namespace.

The file structure
------------------
    \plugins
        \WPMVC
            - index.php - Framework bootstrap
            \Core
                -  splloader.php - SPL loader for PSR-0 standard
                -  WPMvcRouter.php - Router class containing methods for loading controllers etc.
                -  WPPluginMVC.php - Core class for framework, where loaders for view and models are placed
                \controller
                    - Controller.php - Controller class file which every controller should extend from
                \model
                    - CustomPostModel.php - Model for creating and extending a custom post type. The class contains CRUD functionality
                    - Model.php - A basic standard model class

How to create a plugin using the framework?
===========================================
Please see the WPMVC plugin example repository here.