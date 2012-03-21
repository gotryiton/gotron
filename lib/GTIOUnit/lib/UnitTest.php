<?

namespace GTIOUnit;

use PHPUnit_Framework_TestCase,
    Pheanstalk,
    Gotron\Header;

class UnitTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
        \TestApp\TestApplication::initialize();
    }

    public static function tearDownAfterClass() {
        Header::flush();
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
        $pheanstalk = new \Pheanstalk('127.0.0.1');
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
    
}

?>