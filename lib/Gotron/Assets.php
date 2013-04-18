<?

namespace Gotron;

class Assets {

    public static function javascript($object) {
        return static::get_filename($object, 'js', 'js');
    }

    public static function css($object) {
        return static::get_filename($object, 'css', 'css');
    }

    public static function image($object, $absolute = false) {
        return static::get_filename($object, 'images', null, $absolute);
    }

    public static function get_filename($object, $prefix, $extension = null, $absolute = false) {

        $file_info = pathinfo($object);

        if (is_null($extension)) {
            $extension = $file_info['extension'];
            $object = $file_info['filename'];
        }
        elseif (array_key_exists('basename', $file_info)) {
            $object = $file_info['basename'];
        }

        if (array_key_exists('dirname', $file_info) && $file_info['dirname'] !== ".") {
            $object = file_join($file_info['dirname'], $object);
        }

        if($path = Config::get("assets.{$prefix}_location", true)) {
            if (Config::get('assets.hashed', true)) {
                $object = static::hash_object($object, $extension, $prefix);
            }

            $object = $object . ".{$extension}";

            return file_join($path, $object);
        } else if ($absolute) {
            return Config::get("cdn.domain") . "assets/{$prefix}/{$object}.{$extension}";
        } else {
            return "/assets/{$prefix}/{$object}.{$extension}";
        }
    }

    public static function hash_object($object, $extension, $prefix = null) {
        $prefix = is_null($prefix) ? $extension : $prefix;
        $config = Config::instance();
        if (!($dictionary = $config->get('assets_dictionary', true))) {
            $dictionary = file_get_contents(file_join(Config::get('root_directory'), '../../shared/assets.json'));
            $dictionary = json_decode($dictionary, true);
            $config->set('assets_dictionary', $dictionary);
        }

        $sha =  $dictionary[$prefix]["{$object}.{$extension}"];

        return "{$object}_{$sha}";
    }

}

?>
