<?php
namespace ActiveRecord;

class Memcached
{
	const DEFAULT_PORT = 11211;

	private $memcache;

	/**
	 * Creates a Memcache instance.
	 *
	 * Takes an $options array w/ the following parameters:
	 *
	 * <ul>
	 * <li><b>host:</b> host for the memcache server </li>
	 * <li><b>port:</b> port for the memcache server </li>
	 * </ul>
	 * @param array $options
	 */
	public function __construct($options)
	{
        $this->memcache = new \Memcached();
        if(isset($options['servers']) && is_array($options['servers'])){
            foreach($options['servers'] as $server)
            {
                $port = isset($server['port']) ? $server['port'] : self::DEFAULT_PORT;
                if (!$this->memcache->addServer($server['host'],$port))
                    throw new CacheException("Could not connect to $server[host]:$port");
            }
        }
        else{
           $options['port'] = isset($options['port']) ? $options['port'] : self::DEFAULT_PORT;
        
           if (!$this->memcache->addServer($options['host'],$options['port']))
               throw new CacheException("Could not connect to $options[host]:$options[port]");
        }
	}

	public function flush()
	{
		$this->memcache->flush();
	}

	public function read($key)
	{
		return $this->memcache->get($key);
	}

	public function delete($key)
	{
		return $this->memcache->delete($key);
	}

	public function write($key, $value, $expire)
	{
		return $this->memcache->set($key,$value,$expire);
	}

	public function multi_set($list, $expire)
	{
		return $this->memcache->setMulti($list,$expire);
	}
	
	public function multi_get($list)
	{
		return $this->memcache->getMulti($list,$expire);
	}
}
?>
