<?

namespace Gotron;

use Spyc;

class i18n {

    /**
     * The default locale used if one is not specified
     */
    const DEFAULT_LOCALE = "en";

    /**
     * The array containing localized text for the current language
     *
     * @var array
     */
    public $yaml_text = array();

    public function __construct($language = NULL) {
        $this->set_default_locale($language);
    }

    /**
     * Sets the default locale based on configuration and DEFAULT_LOCALE constant
     *
     * @param string $language
     * @return void
     */
    protected function set_default_locale($language = NULL) {
        if (empty($language)) {
            if (!($language = Config::get('default_locale', true))) {
                $language = self::DEFAULT_LOCALE;
            }
        }

        $this->set_yaml($language);
    }

    /**
     * Sets the yaml_file for the specified language
     *
     * @param string $language
     * @return void
     */
    protected function set_yaml($language) {
        $yaml_file = file_join(Config::get('root_directory'), Config::get('localization_directory'), "$language.yaml");
        if (file_exists($yaml_file)) {
            $this->yaml_text = Spyc::YAMLLoad($yaml_file);
        }
        else {
            throw new Exception("Localization file does not exist for language: $language");
        }
    }

    /**
     * Gets the text at the specified key
     *
     * @param string $identifier
     * @param string $key
     * @return string
     */
    protected function read_yaml($identifier, $key) {
        if (array_key_exists($identifier, $this->yaml_text)) {
            if (array_key_exists($key, $this->yaml_text[$identifier])) {
                return $this->yaml_text[$identifier][$key];
            }
            else {
                throw new Exception("Key: $key does not exist in identifier: $identifier");
            }
        }
        else {
            throw new Exception("Identifier: $identifier does not exist in the localization definition");
        }
    }

    /**
     * Reads the yaml file for the specified identifier and key
     *
     * @param string $identifier
     * @param string $key
     * @return string
     */
    public function get_text($identifier, $key) {
        return $this->read_yaml($identifier, $key);
    }

    /**
     * Static method to get the text at identifier, key
     *  defaults to the language aleady set
     *
     * @param string $identifier
     * @param string $key
     * @param string $language
     * @return string
     */
    public static function text($identifier, $key, $language = NULL) {
        $intstance = new self($language);
        return $intstance->get_text($identifier, $key);
    }

}

?>
