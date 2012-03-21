<?php

namespace Gotron;

use \ArrayAccess,
    \Spyc;

class Config extends Singleton implements ArrayAccess {
	/**
	 * Constants
	 */
	const DEFAULT_ENVIRONMENT 		        = "development";
	const DEFAULT_HOST 	                    = "localhost";
	const DEFAULT_PORT           	        = "3306";
	const DEFAULT_USER 	                    = "root";
	const DEFAULT_PASSWORD                  = "";
	const DEFAULT_DATABASE 			        = "gtioStage";
	const DEFAULT_APP_VERSION 		        = "0ad335cdbc6611da404f80266f2d5b7cf199150d";
    const DEFAULT_CONFIG_DIRECTORY          = "config";
    const DEFAULT_MODEL_DIRECTORY           = "app/models";
    const DEFAULT_VIEW_DIRECTORY            = "app/views";
    const DEFAULT_LOCALIZATION_DIRECTORY    = "config/localization";

	/**
	 * The application environment
	 *
	 * @var string
	 */
	public $environment;

    /**
     * Used for ArrayAccess behavior
     *
     * @var array
     */
    private $access_array = array();

	/**
	 * Array of routes
	 *
	 * @var array
	 */
	public $routes = array();

	/**
	 * Calls the Closure function with the config instance
	 *
	 * @param Closure $initializer 
	 * @return void
	 */
	public static function initialize($function) {
		$function(parent::instance());
	}

	/**
	 * Loads all the necessary config settings
	 *
	 * @param Closure $config 
	 * @return Config
	 */
	public static function load_config($config) {
		$instance = self::instance();
        $instance->load_default_directories();
        $instance->set_environment(self::get_environment());
        $instance->set_root_directory(realpath(__DIR__ . "/../../../../"));
        static::initialize($config);
        $instance->define_app_version();
		$instance->load_environment_config($instance->environment);
		$instance->load_database_config($instance->environment);
		return $instance;
	}

	/**
	 * Loads the application configuration for the environment
	 *
	 * @param string $environment 
	 * @return void
	 */
	public function load_environment_config($environment) {
		$this->load_environment_file($environment);
	}

	/**
	 * Loads the extended environment to allow configuration inheritance 
	 *
	 * @param string $environment 
	 * @return void
	 */
	public function extends_environment($environment) {
		$this->load_environment_file($environment);
	}
	
	/**
	 * Requires the environment file with name $environment.php
	 *
	 * @param string $environment 
	 * @return void
	 */
	protected function load_environment_file($environment) {
		require file_join($this->root_directory, $this->config_directory, "environments", $environment . ".php");
	}
	
	/**
	 * Sets the application environment
	 *
	 * @param string $environment 
	 * @return void
	 */
	public function set_environment($environment) {
		$this->environment = $environment;
	}
	
	/**
	 * Sets the root directory of the app
	 *
	 * @param string $root_directory 
	 * @return void
	 */
	public function set_root_directory($root_directory) {
		$this->root_directory = $root_directory;
	}
	
	/**
	 * Checks for the APP_ENVIRONMENT variable
	 *
	 * @return string
	 */
	public static function get_environment() {
		if(array_key_exists("APP_ENVIRONMENT", $_ENV)) {
			return $_ENV["APP_ENVIRONMENT"];
        }
		else if(defined("APP_ENVIRONMENT")) {
            return APP_ENVIRONMENT;
        }
        else{
            return self::DEFAULT_ENVIRONMENT;
        }
	}

	/**
	 * Sets the configuration for the application version
	 *  uses the capistrano revision file if possible
	 *
	 * @return string app_version
	 */
	public function define_app_version() {
		$asset_revision_file = file_join(static::get('root_directory'), 'ASSET_REVISION');
		if(file_exists($asset_revision_file)) {
			return $this->set('app_version', file_get_contents($asset_revision_file));
		}
		else if(array_key_exists("Gotron_APP_VERSION", $_ENV)) {
			return $this->set('app_version', $_ENV["APP_ENVIRONMENT"]);
		}
		else {
			return $this->set('app_version', static::DEFAULT_APP_VERSION);
		}
	}
	
	/**
	 * Loads the database settings into the config instance
	 *
	 * @return void
	 */
	public function load_database_config() {
		$database_parameters = array("database", "user", "password", "host", "port");
		$this->load_from_yaml($database_parameters, file_join(static::get('root_directory'), "config/database.yml"));
	}

    /**
     * Sets the default directories
     *
     * @return void
     */
    public function load_default_directories() {
        $this->config_directory = self::DEFAULT_CONFIG_DIRECTORY;
        $this->model_directory = self::DEFAULT_MODEL_DIRECTORY;
        $this->view_directory = self::DEFAULT_VIEW_DIRECTORY;
        $this->localization_directory = self::DEFAULT_LOCALIZATION_DIRECTORY;
    }

	/**
	 * Loads variables from either Yaml file or class constants
	 *
	 * @param string $params 
	 * @return void
	 */
	public function load_from_yaml($params, $yaml_file) {
		
		if(!is_array($params)) {
			$params = array($params);
        }

        if(file_exists($yaml_file)) {
            $yaml = Spyc::YAMLLoad($yaml_file);
            $config = $yaml[$this->environment];
            foreach($params as $param) {
                if(array_key_exists($param, $config)) {
                    $this->set($param, $config[$param]);
                }
                else {
                    $this->set($param, constant('self::DEFAULT_' . strtoupper($param)));
                }
            }
        }
	}

	/**
	 * Gets the value of $property
	 *
	 * @param string $property 
     * @param bool $bool set to true to return false if key is not set
     *  use of this can have odd circumstances as a property might have a boolean value
	 * @return mixed Value of property for config
	 */
	public static function get($property, $bool = false) {
        if($value = static::get_key($property)) {
			return $value;
		}
        else {
            if($bool) {
                return false;
            }
            else {
                throw new Exception("Configuration property $property is not set");
            }
        }
	}

	/**
	 * Returns boolean value of a configuration key
	 *   (returns false if the key does not exist)
	 *
	 * @param string $property 
	 * @return bool
	 */
	public static function bool($property) {
		if($value = static::get_key($property)) {
			return (bool) $value;
		}
		else {
			return false;
		}
	}

	/**
	 * Gets the value for a key
	 *
	 * @param string $key 
	 * @return void
	 */
	protected static function get_key($property) {
		$instance = self::instance();
        if(property_exists($instance, $property)){
            return $instance->$property;
        }

        $namespaced = explode('.', $property);
        $value = $instance->access_array;

		$break = false;
        foreach($namespaced as $key) {
			if(array_key_exists($key, $value)) {
				$value = $value[$key];
			}
			else {
				$break = true;
				break;
			}
        }

        if(isset($value) && !$break) {
            return $value;
        }
        else if(property_exists($instance, strtoupper($property))) {
            $property = strtoupper($property);
            return $instance->$property;
        }
		else {
			return false;
		}
	}

    /**
     * Sets the key (namespaced) to value (as used in Propel Orm)
     *
     * @param string $key 
     * @param string $value 
     * @return value
     */
    public function set($key, $value) {
        $access = &$this->access_array;
        $parts = explode('.', $key);
        foreach($parts as $part) {
            $access = &$access[$part];
        }
        $access = $value;
        return $value;
    }

	/**
	 * Sets a constant in the global namespace
	 *
	 * @param string $name 
	 * @param string $value 
	 * @return string
	 */
	public function set_constant($name, $value) {
		if(!defined($name)) {
			define($name, $value);
			return $value;
		}
		else {
			return constant($name);
		}
	}

    /**
     * Magic method to set the access_array properly
     *
     * @param string $key 
     * @param string $value 
     * @return void
     */
    public function __set($key, $value) {
        return $this->set($key, $value);
    }

    /**
     * Magic method to get the key using access array
     *
     * @param string $key 
     * @return void
     * @author 
     */
    public function __get($key) {
        if(property_exists($this, $key)) {
            return $this->$key;
        }
        else if(array_key_exists($key, $this->access_array)) {
            return $this->access_array[$key];
        }
        else{
            throw new Exception("Property $key does not exist for object");
        }
    }

    /**
     * ArrayAccess method, checks if offset exists
     *
     * @param string $index 
     * @return bool
     */
    public function offsetExists($index) {
        return isset($this->access_array[$index]);
    }
 
    /**
     * ArrayAccess method, returns value at offset
     *
     * @param string $index 
     * @return mixed 
     */
    public function offsetGet($index) {
        if($this->offsetExists($index)) {
            return $this->access_array[$index];
        }
        return false;
    }
 
    /**
     * ArrayAccess method, sets value at offset
     *
     * @param string $index 
     * @param string $value 
     * @return bool 
     */
    public function offsetSet($index, $value) {
        if(is_null($index)) {
            $this->access_array[] = $value;
            return true;
        }
        else {
            $this->access_array[$index] = $value;
            return true;
        }
    }
 
    /**
     * ArrayAccess method, unset the offset
     *
     * @param string $index 
     * @return bool
     */
    public function offsetUnset($index) {
        unset($this->access_array[$index]);
        return true;
    }

}

?>