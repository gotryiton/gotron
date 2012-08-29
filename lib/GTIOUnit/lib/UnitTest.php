<?

namespace GTIOUnit;

use PHPUnit_Framework_TestCase,
    Pheanstalk,
    Gotron\Header,
    Gotron\Dispatch\Router,
    PHPUnit_Framework_Constraint_IsTrue,
    __;

class UnitTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
        \TestApp\TestApplication::initialize();
    }

    public static function tearDownAfterClass() {
        Header::flush();
    }

    public function run(\PHPUnit_Framework_TestResult $result = null) {
        $this->setPreserveGlobalState(false);
        return parent::run($result);
    }

    /**
     * Clears a folder of all files
     *
     * @param string $folder 
     * @return void
     * @author 
     */
    protected static function clear_folder($folder, $ignore = array('.gitignore')) {
        if(!$dh = @opendir($folder)) return;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..' || in_array($obj, $ignore)) {
                continue;
            }
            unlink($folder.'/'.$obj);
        }
        closedir($dh);
        return true;
    }

    /**
     * Clears the beanstalk queue
     *
     * @return void
     * @author 
     */
    protected static function clear_beanstalk() {
        $pheanstalk = new Pheanstalk('127.0.0.1');
        $stats = $pheanstalk->stats();
        $jobs = $stats['current-jobs-ready'];
        foreach($pheanstalk->listTubes() as $tube){
            $pheanstalk->watch($tube);
        }
        while($jobs > 0){
            $job = $pheanstalk->reserve();
            $pheanstalk->delete($job);
            $stats = $pheanstalk->stats();
            $jobs = $stats['current-jobs-ready'];
        }
    }

    protected function assertValidAttributes($valid_attributes, $object) {
        $message = '';
        $condition = true;

        if(method_exists($object,'attributes') && $keys = $object->attributes()) {
            $object = $keys;
            $objectKeys = __($keys)->keys();
        }
        else {
            $objectKeys = __($object)->keys();
        }
        foreach($valid_attributes as $key => $value) {
            if(__()->includ($objectKeys,$key)) {
                if($object[$key] != $value) {
                    $message = "Value {$object[$key]} for '{$key}' does not match expected value {$value}";
                    $condition = false;
                    break;
                }
            }
            else {
                $message = "Key '$key' does not exist in tested object";
                $condition = false;
                break;
            }
        }

        $this->assertThat($condition, self::isTrue(), $message);
    }

    public static function isTrue() {
        return new PHPUnit_Framework_Constraint_IsTrue;
    }

    public static function application() {
        return \Gotron\Application::instance();
    }

    public static function get($path, $params = [], $override_headers = []) {
        return static::request($path, 'get', $params, $override_headers);
    }

    public static function post($path, $params = [], $override_headers = []) {
        return static::request($path, 'post', $params, $override_headers);
    }

    public static function request($path, $method, $params, $override_headers) {
        $app = static::application();
        $version = constant(get_class($app) . "::VERSION");

        $headers = [
                'Accept' => "application/v{$version}-json"
            ];

        foreach ($headers as $key => $option) {
            if (array_key_exists($key, $override_headers)) {
                $headers[$key] = $override_headers[$key];
            }
        }

        return Router::find_route_and_get_response($path, $app, ['params' => $params, 'headers' => $headers, 'method' => $method]);
    }

}

?>
