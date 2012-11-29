<?

namespace Gotron;

/**
 * Logs messages to syslog
 *
 * messages are written with the following format "hh:mm:ss (script name) $message"
 *
 * @package Gotron
 */
class Logging {

    private $log_level = 1; // 1: Standard, 2: Verbose

    public $do_log = true;

    public $type = "SYSLOG";

    public $log_name;

    /**
     * Set the log name and open a file pointer to the file
     *
     * @param string $logFile
     */
    public function __construct($log_name = "default", $log_message = null) {
        if ($log_name == 'STDOUT') {
            $this->type = 'STDOUT';
        }
        else {
            $this->type = 'SYSLOG';
        }

        $this->log_name = "##$log_name##";

        if (isset($log_message)) {
            $this->lwrite($log_message);
        }
    }

    /**
     * Logs to syslog
     *
     * @param string $message
     * @param string $tag
     * @param string $level
     * @return void
     */
    public function log($message) {
        $this->lwrite($message);
    }

    /**
     * Static function to write to the log
     *
     * @param string $message
     * @param string $log_name
     * @return void
     */
    public static function write($message, $log_name = "default"){
        $instance = new self($log_name);
        $instance->lwrite($message);
    }

    /**
     * Write to the log file
     *
     * @param string $message
     * @param string $level
     * @return void
     */
    public function lwrite($message, $level = 1) {
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $unique_id = $GLOBALS['unique_id'];
        $message = "[{$unique_id}] [$script_name] $message";
        if ($this->do_log && $level <= $this->log_level) {
            if ($this->type == "STDOUT") {
                echo $message . "\n";
            }
            else {
                openlog($this->log_name, 0, LOG_LOCAL0);
                syslog(LOG_WARNING, $message);
                closelog();
                return;
            }
        }
    }

}

?>
