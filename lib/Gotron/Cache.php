<?

namespace Gotron;

/**
 * Caching class with additional key naming functionality
 *
 * @package Gotron
 */
class Cache extends \ActiveRecord\Cache {

    public static function get($key, $closure, $ttl = 0) {
        $real_key = static::get_key($key);

        return parent::get($real_key, $closure, $ttl);
    }

    public static function get_key($key) {
        return join("/", static::key_parts($key));
    }

    public static function md5_key($key) {
        return md5(static::get_key($key));
    }

    protected static function key_parts($key) {
        if (!is_array($key)) {
            $key = array($key);
        }

        foreach ($key as $piece) {
            if (is_object($piece)) {
                // check for the property 'cache_key' and the method 'cache_key()'
                if (method_exists($piece, 'cache_key')) {
                    $key_pieces[]= $piece->cache_key();
                }
                elseif (property_exists($piece, 'cache_key')) {
                    $key_pieces[]= $piece->cache_key;
                }
                else {
                    // Just so that we can at least get something here
                    $key_pieces[]= get_class($piece);
                }
            }
            else {
                $key_pieces[]= $piece;
            }
        }

        return $key_pieces;
    }

}

?>
