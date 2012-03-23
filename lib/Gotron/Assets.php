<?

namespace Gotron;

class Assets {

    public static function javascript($object) {
        if($path = Config::get('assets.js_location', true)) {
            return file_join($path, $object);
        }
        else {
            return "/assets/javascripts/$object";
        }
    }
    
    public static function css($object) {
        if($path = Config::get('assets.css_location', true)) {
            return file_join($path, $object);
        }
        else {
            return "/assets/css/$object";
        }
    }
    
}

?>