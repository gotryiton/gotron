<?php
namespace ActiveRecord;
use Closure;

/**
 * Cache::get('the-cache-key', function() {
 *	 # this gets executed when cache is stale
 *	 return "your cacheable datas";
 * });
 */
class Cache
{
	static $adapter = null;
	static $options = array();

	/**
	 * Initializes the cache.
	 *
	 * With the $options array it's possible to define:
	 * - expiration of the key, (time in seconds)
	 * - a namespace for the key
	 *
	 * this last one is useful in the case two applications use
	 * a shared key/store (for instance a shared Memcached db)
	 *
	 * Ex:
	 * $cfg_ar = ActiveRecord\Config::instance();
	 * $cfg_ar->set_cache('memcache://localhost:11211',array('namespace' => 'my_cool_app',
	 *																											 'expire'		 => 120
	 *																											 ));
	 *
	 * In the example above all the keys expire after 120 seconds, and the
	 * all get a postfix 'my_cool_app'.
	 *
	 * (Note: expiring needs to be implemented in your cache store.)
	 *
	 * @param string Or array $servers
	 * @param array $options Specify additional options
	 */
	public static function initialize($servers, $options=array('type' => 'memcached'))
	{
	    if(!array_key_exists('type',$options))
	        $options['type'] = 'memcached';
        if(is_array($servers)){
            $file = ucwords($options['type']);
			$class = "ActiveRecord\\$file";
			require_once __DIR__ . "/cache/$file.php";
			static::$adapter = new $class(array('servers' => $servers));
        }
		else if ($servers)
		{
			$url = parse_url($servers);
            $file = ucwords($options['type']);
			$class = "ActiveRecord\\$file";
			require_once __DIR__ . "/cache/$file.php";
			static::$adapter = new $class($url);
		}
		else
			static::$adapter = null;

		static::$options = array_merge(array('expire' => 30, 'namespace' => ''),$options);
	}

	public static function flush()
	{
		if (static::$adapter)
			static::$adapter->flush();
	}

	public static function get($key, $closure, $ttl = null)
	{
		$key = static::get_namespace() . $key;
		
		if (!static::$adapter)
			return $closure();

		if (!($value = static::$adapter->read($key))){
            $expire = (!is_null($ttl)) ? $ttl : static::$options['expire'];
			static::$adapter->write($key,($value = $closure()), $expire);
		}
		
		return $value;
	}

    public static function fetch($key)
	{   
	    if (!static::$adapter)
	        return false;
		$key = static::get_namespace() . $key;
		return static::$adapter->read($key);
	}
	
	public static function set($key,$value,$expire = null)
	{
	    if (!static::$adapter)
	        return false;
	        
	    $key = static::get_namespace() . $key;

        $expire = (!is_null($expire)) ? $expire : static::$options['expire'];
	    static::$adapter->write($key,$value,$expire);
	}
	
	public static function delete($key)
	{
	    if (!static::$adapter)
	        return false;
	        
	    $key = static::get_namespace() . $key;
	    
	    return static::$adapter->delete($key);
	}

	public static function multi_set($list,$expire = null)
	{
	    if (!static::$adapter)
	        return false;

        foreach ($list as $k => $value) {
           unset ($list[$k]);

           $key = static::get_namespace() . $k;

           $list[$key] = $value;
        }

	    return static::$adapter->multi_set($list,$expire);
	}

	protected static function get_namespace()
	{
		return (isset(static::$options['namespace']) && strlen(static::$options['namespace']) > 0) ? (static::$options['namespace'] . "::") : "";
	}

	public static function multi_get($list)
	{
	    if (!static::$adapter)
	        return false;

        $namespaced_list = array();
        foreach ($list as $key) {
           unset ($list[$key]);
           $key = static::get_namespace() . $key;
           $namespaced_list[]= $key;
        }

	    return static::$adapter->multi_get($namespaced_list);
	}
}
?>
