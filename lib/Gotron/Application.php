<?

namespace Gotron;

use ActiveRecord,
    Aura\Autoload\Loader;

class Application extends Singleton {

	public $config;
	
	/**
	 * Bootstraps the application
	 *
	 * @return void
	 */
	public static function initialize() {
		$instance = static::instance();
        $instance->autoload_library();
		$config = Config::load_config(static::configuration());
		$instance->config = $config;
        $instance->autoload_app();
		$instance->check_maintenance();
        $instance->autoload_config();
		self::initialize_active_record($config);
        self::initialize_routes();
	}

    /**
     * Autoload the library requirements
     *
     * @return void
     * @author 
     */
    public function autoload_library() {
        $loader = new Loader;
        $loader->register();
        $loader->setNamespacedPaths(array(
            'Gotron\\' => file_join(__DIR__, ".."),
            'ActiveRecord\\' => file_join(__DIR__, "/../vendor/ActiveRecord/lib"),
            'Requests_' => file_join(__DIR__, "/../vendor/Requests"),
            'Swift_' => file_join(__DIR__, "/../vendor/Swift/classes"),
            'Pheanstalk_' => file_join(__DIR__, "/../vendor/Pheanstalk/classes")
        ));

        $loader->setClasses(array(
           'Requests' => file_join(__DIR__, "/../vendor/Requests/Requests.php"),
           'Swift' => file_join(__DIR__, "/../vendor/Swift/classes/Swift.php"),
           'Pheanstalk' => file_join(__DIR__, "/../vendor/Pheanstalk/classes/Pheanstalk.php")
        ));

        $loader->setPlainPaths(array(
            file_join(__DIR__, "/../vendor/"),
        ));
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
     * Defines any additional autoloads
     *
     * @param array $array
     * @return void
     */
    public static function define_autoloads(array $array) {
        $instance = static::instance();
		$config = $instance->config();
        $loader = new Loader;
        $loader->register();
        $loader->setClasses($array);
    }

    /**
     * Autoloads the app paths
     *
     * @return void
     */
    public function autoload_app() {
        $root_directory = $this->config->get('root_directory');

        $loader = new Loader;
        $loader->register();
        $loader->addFrameworkClassPaths(array(
            file_join($root_directory, "app", "controllers"),
        	file_join($root_directory, "app", "jobs"),
        	file_join($root_directory, "app", "models"),
        	file_join($root_directory, "app", "modules"),
        	file_join($root_directory, "app", "views")
        ), $this->config->get('namespace'));
    }

	/**
	 * Calls the ActiveRecord initializer with the settings in app_config 
	 *
	 * @param string $app_config 
	 * @return void
	 */
	public static function initialize_active_record($app_config) {
        require_once __DIR__ . '/../vendor/ActiveRecord/lib/ActiveRecord/Utils.php';
		ActiveRecord\Config::initialize(function($cfg) use($app_config) {
		    $logger = \Log::singleton('syslog',LOG_LOCAL0,'##QUERY_LOG##',array('timeFormat' =>  '%Y-%m-%d %H:%M:%S'));
		    $cfg->set_logging($app_config->bool('db.query_logging'));
		    $cfg->set_logger($logger);
		    $cfg->set_model_directory($app_config->model_directory);
			$cfg->set_default_connection("environment");
		    $cfg->set_connections(array(
				"environment" => 'mysql://' . $app_config->username . ':' . $app_config->password . '@' . $app_config->host .'/' . $app_config->database));

            if($cache_servers = $app_config->get('cache.servers', true)) {
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
		if(file_exists(file_join(Config::get('root_directory'), '../MAINTENANCE'))){
			$this->config->set('show_maintenance', true);
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
		return function($config){};
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
        if(!is_null($key)) {
            return $config->get($key);
        }
        else{
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
}

?>