<?

namespace Gotron\Beanstalker;

use Pheanstalk_Pheanstalk as Pheanstalk,
    Pheanstalk_Connection,
    Gotron\Logging,
    Gotron\Config;

/**
 * Beanstalker
 * Class used to manage Beanstalk Queues
 * Derived from Resque (https://github.com/defunkt/resque) and PHP-Resque (https://github.com/chrisboulton/php-resque)
 *
 * @package Gotron
 */

class Beanstalker extends Pheanstalk {
    const DEFAULT_HOST = "127.0.0.1";
    const DEFAULT_PORT = 11300;

    public $logLevel = 1; // 1: Standard, 2: Verbose
    public $doLog = true;

    public function __construct($host = null, $port = null, $timeout = null) {
        if (is_null($host)) {
            $host = (Config::get('beanstalk.host', true)) ? Config::get('beanstalk.host') : self::DEFAULT_HOST;
        }

        if (is_null($port)) {
            $port = (Config::get('beanstalk.port', true)) ? Config::get('beanstalk.port') : self::DEFAULT_PORT;
        }

        $this->setConnection(new Pheanstalk_Connection($host, $port, $timeout));
    }

    public function setLog($location) {

        if ($location == 'STDOUT') {
            $this->log = new Logging('STDOUT');
        }
        else {
            $this->log = new Logging($location);
        }

    }

    public function setLogLevel($level) {
        $this->log->setlogLevel($level);
    }

}
?>
