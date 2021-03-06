<?

namespace Gotron;

use ActiveRecord,
    Gotron\Dispatch\Router,
    Gotron\Util\Version;

class Application extends Singleton {

    const VERSION = 1;

    public $config;

    public $version;

    /**
     * Bootstraps the application
     *
     * @return void
     */
    public static function initialize() {
        $instance = static::instance();
        $instance->define_unique_id();
        $config = Config::load_config(static::configuration());
        $instance->config = $config;
        $instance->autoload_app();
        $instance->check_maintenance();
        $instance->autoload_config();
        $instance->load_helpers();
        self::initialize_active_record($config);

        return $instance;
    }

    /**
     * Load the config/autoload.php file
     *
     * @return void
     */
    public function autoload_config() {
        require file_join($this->config->get('root_directory'), $this->config->config_directory, "autoload.php");
    }

    /**
     * Loads any files in the app/helpers directory
     *
     * @return void
     */
    public function load_helpers() {
        $dir = file_join($this->config->get('root_directory'), 'app', 'helpers');
        if (is_dir($dir)) {
            foreach (glob($dir . "/*.php") as $filename) {
                require_once $filename;
            }
        }
    }

    /**
     * Defines any additional autoloads
     *
     * @param array $array
     * @return void
     */
    public static function define_autoloads(array $array) {
        $instance = static::instance();
        $instance->loader->register();
        foreach ($array as $class_name => $path) {
            if (is_array($path) && array_key_exists('dir', $path)) {
                $instance->loader->add($class_name, $path['dir']);
            }
            elseif (is_array($path) && array_key_exists('class', $path)) {
                $instance->loader->add($class_name, $path['class']);
            }
            else {
                $instance->loader->setClass($class_name, $path);
            }
        }
    }

    /**
     * Autoloads the app paths
     *
     * @return void
     */
    public function autoload_app() {
        $this->loader = new Loader;
        $this->loader->setMode(0);
        $this->loader->register();

        $root_directory = $this->config->get('root_directory');

        $this->loader->addFrameworkClassPaths(array(
            file_join($root_directory, "app", "controllers"),
            file_join($root_directory, "app", "jobs"),
            file_join($root_directory, "app", "models"),
            file_join($root_directory, "app", "modules"),
            file_join($root_directory, "app", "views")
        ), $this->config->get('namespace'));

        $this->loader->register();
    }

    /**
     * Allows for autoloading paths based on the requested version number
     * defaults to the version number constant of the application. This
     * should be overridden in config/application.php
     *
     * @param string $version
     * @return void
     */
    public function version_by_request($request_version = null) {
        $request_version = is_null($request_version) ? Version::parse(static::VERSION) : $request_version;

        $root_directory = $this->config->get('root_directory');
        $presenter_directory = file_join($this->config->get('root_directory'), "app", "presenters");
        $directories = glob("{$presenter_directory}/*", GLOB_ONLYDIR);

        $all_versions = [];
        foreach ($directories as $directory) {
            $name = str_replace("v", "", basename($directory));
            $all_versions[$name] = Version::parse($name);
        }

        $versions = array_filter($all_versions, function($version) use ($request_version) { return $version->lt_eq($request_version); });
        uasort($versions, "Gotron\Util\Version::compare_versions");

        $this->loader->addFrameworkClassPaths(
            array_map(function($version) use ($presenter_directory) {
                return file_join($presenter_directory, "v{$version}");
            }, array_keys($versions))
        , $this->config->get('namespace'));
    }

    /**
     * Calls the ActiveRecord initializer with the settings in app_config
     *
     * @param string $app_config
     * @return void
     */
    public static function initialize_active_record($app_config) {
        ActiveRecord\Config::initialize(function($cfg) use($app_config) {
            $logger = new Logging('QUERY_LOG');
            $cfg->set_logging($app_config->bool('db.query_logging'));
            $cfg->set_logger($logger);
            $cfg->set_model_directory($app_config->model_directory);
            $cfg->set_default_connection("environment");
            $cfg->set_connections(array(
                "environment" => 'mysql://' . $app_config->username . ':' . $app_config->password . '@' . $app_config->host .'/' . $app_config->database
            ));

            if ($cache_servers = $app_config->get('cache.servers', true)) {
                $cfg->set_cache($cache_servers, array("expire" => 60,'namespace' => $app_config->environment));
            }
        });
    }

    /**
     * Checks if the Maintenance screen file exists and sets the configuration key
     *
     * @return void
     */
    public function check_maintenance() {
        if (file_exists(file_join(Config::get('root_directory'), '../../MAINTENANCE'))) {
            $this->config->set('show_maintenance', true);
        }
        else {
            $this->config->set('show_maintenance', false);
        }
    }

    /**
     * Calls the closure with the config for the application
     *
     * @param Closure $application
     * @return void
     */
    public static function configure($function) {
        $function(Config::instance());
    }

    /**
     * Override this function to define default attributes to be used in the config
     * Needs to return a closure
     *
     * @return Closure
     */
    public static function configuration() {
        return function($config) {};
    }

    /**
     * Returns the config setting or config instance if no config key is set
     *
     * @param string $name
     * @return mixed
     */
    public static function config($key = null) {
        $instance = static::instance();
        $config = $instance->config;
        if (!is_null($key)) {
            return $config->get($key);
        }
        else {
            return $config;
        }
    }

    /**
     * Set the routes array inside Config
     *
     * @param array $routes
     * @return array $routes
     */
    public static function define_routes($routes) {
        $instance = static::instance();
        $config = $instance->config();
        $config->routes = $routes;
    }

    /**
     * Requires the routes file
     *
     * @return void
     */
    public static function initialize_routes() {
        $instance = static::instance();
        $config = $instance->config();
        require file_join($config->root_directory, $config->config_directory, "routes.php");
    }

    public static function route() {
        Router::route(static::instance());
    }

    protected function define_unique_id() {
        $GLOBALS['unique_id'] = md5(uniqid(gethostname(), true));
    }

}

?>
