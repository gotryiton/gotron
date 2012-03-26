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
	private $fp = null;
	
	private $logLevel = 1; // 1: Standard, 2: Verbose 
	
	public $doLog = true;

    public $type = "SYSLOG";
	
	/**
	 * Set the log name and open a file pointer to the file
	 *
	 * @param string $logFile 
	 */
	public function __construct($log_name, $log_message = null){
        if($log_name == 'STDOUT') {
            $this->type = 'STDOUT';
        }
        else {
            $this->type = 'SYSLOG';
        }
		$this->log_name = "##$log_name##";
		if (isset($logMessage)){
			$this->lwrite($logMessage);
		}
	}

    /**
     * Static method to log to syslog
     *
     * @param string $message 
     * @param string $tag 
     * @param string $level 
     * @return void
     */
	public static function log($message, $tag = "default", $level = 1) {
	    $log_name = "##$tag##";
		if (isset($message)) {
		    $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
		    openlog($log_name,0,LOG_LOCAL0);
            syslog(LOG_WARNING,"[$script_name] $message");
            closelog();
		    return;
		} 
	}
	
	/**
	 * Sets the level of the log
	 *
	 * @param string $level 1 for standard 2 for verbose
	 * @return void
	 */
	public function setLogLevel($level) {
	  $this->logLevel = $level;
	}
	
    /**
     * Write to the log file
     *
     * @param string $message 
     * @param string $level 
     * @return void
     */
	public function lwrite($message, $level = 1){
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $message = "[$script_name] $message";
        if($this->doLog && $level <= $this->logLevel) {
            if($this->type == "STDOUT") {
                echo $message . "\n";
            }
            else{
                openlog($this->log_name, 0, LOG_LOCAL0);
                syslog(LOG_WARNING, $message);
                closelog();
                return;
            }
        }
    }
    
    public function write($message,$level = 1){
	    $this->lwrite($message, $level);
    }
}
?>