<?

namespace Gotron\Beanstalker;

use Pheanstalk_Connection,
    Gotron\Config,
    Gotron\Exception;

/**
 * BeanstalkerWorker
 *
 * @package Gotron\Beanstalker
 */
class BeanstalkerWorker extends Beanstalker {

	protected $currentJob;
	protected $stop;
	protected $paused;
	protected $queues;
	protected $numProcessed = 0;
	
	/**
	 * initialize a worker
	 *
	 * @param array $queues 
	 * @param string $host 
	 * @param integer $port 
	 * @param integer $timeout
	 */
	public function __construct($queues = array('default'), $host = null, $port = null, $timeout = null) {
        parent::__construct($host, $port, $timeout);
		if(!empty($queues) && is_array($queues)) {
			$this->queues = $queues;
		}
		else {
			throw new Exception("Queue name cannot be empty");
		}
	}
	
	/**
	 * Reserve the next job from beanstalk
	 *
	 * @return BeanstalkerJob
	 */
	public function reserveJob() {
        $job = BeanstalkerJob::reserveJob($this->queues);
		if($job) {
		    $this->currentJob = $job;
            return true;
		}
		return false;
	}
	
	/**
	 * Wait for jobs and perform work. Long running process
	 *
	 * @param integer $interval 
	 * @return void
	 */
	public function work($interval = 5) {
        $this->registerSigHandlers();
        $this->log->lwrite("Worker started...");
        while(true) {
            if($this->stop) {
                break;
            }

            if(!$this->paused) {
                $this->reserveJob(); 
            }

            if(!$this->currentJob) {
                if($interval == 0) {
                    //for testing purposes
                    break;
                }
                //either no job or worker may be paused either way, wait for the interval
                $this->log->lwrite("Waiting $interval seconds...",2);
                usleep($interval * 1000000);
                continue;
            }
            $this->child = $this->fork();
            if($this->child === 0 || $this->child === false) {
                $status = 'Processing child at ' . strftime('%F %T');
                $this->log->lwrite($status,2);
                $status = $this->perform($this->getCurrentJob());
                if($this->child === 0 && !Config::bool('beanstalk.testing')) {
                    exit(0);
                }
            }

            if($this->child > 0) {
                // Parent process, sit and wait
                $status = 'Forked at ' . strftime('%F %T');
                $this->log->lwrite($status,2);
                if(Config::bool('beanstalk.testing')) {
                    $exitStatus = 0;
                }
                else {
                    $exitStatus = pcntl_wexitstatus($status);
                    pcntl_wait($status);
                }
                $this->incrementProcessed();
                if($exitStatus !== 0) {
                    $this->releaseJob();
                    $this->log->lwrite("Bad exit for forked process");
                }
            }
            $this->child = null;
            $this->currentJob = NULL;
            usleep(100);
        }
        $this->log->lwrite('Worker stopped...');
	}
	
	/**
	 * Peforms the job
	 *
	 * @param BeanstalkerJob $job 
	 * @return void
	 */
	private function perform(BeanstalkerJob $job) {
        try {
            $this->log->lwrite("Performing Job Id: " . $job->getJobId());
            $result = $job->perform();
            if($result === true) {
                $this->log->lwrite("Successfully performed Job Id: " . $job->getJobId());
                $this->child = 1;

                return true;
            }
            else {
                $this->log->lwrite("Exception: $result");
                $this->releaseJob($job->getJobId());
                return false;
            }
        }
        catch(Exception $e) {
            $this->log->lwrite("Exception: $e");
            $this->releaseJob($job->getJobId());
            return false;
        }

        return true;
	}
	
	/**
	 * Release the current job back to the queue or delete the job
	 * 
	 * @return void
	 */
	
	private function releaseJob() {
		$failNum = 5;
		$jobId = $this->currentJob->getJobId();
		$jobStats = $this->statsJob($jobId);
		$releases = (int)$jobStats->__get('releases');
	    if(($releases + 1) >= $failNum) {
	    	$this->currentJob->deleteJob();
		    $this->currentJob = null;
		    $this->log->lwrite("Deleting Job Id " . $jobId . " failed and released $failNum times");
	    }
	    else {
	    	$this->currentJob->releaseJob();
	      	$this->log->lwrite("Failure, released Job Id: " . $jobId . " - release " . $releases);
	    }
		return true;
	}	
  /**
   * Fork the child worker
   *
   * @return void
   */
	
	private function fork() {
        if(Config::bool('beanstalk.testing')) {
            return 0;
        }

		if(!function_exists('pcntl_fork')) {
			return false;
		}

		$pid = pcntl_fork();
		if($pid === -1) {
			throw new RuntimeException('Unable to fork child worker.');
		}

		return $pid;
	}
	
	/**
	 * Get the queues this worker is working on
	 *
	 * @return void
	 */
	public function getQueues() {
		return $this->queues;
	}
	
	/**
	 * Get the current job the worker is working on
	 *
	 * @return BeanstalkerJob
	 */
	public function getCurrentJob() {
		return $this->currentJob;
	}
	
	/**
	 * Increment the number of processed jobs (just used for testing)
	 *
	 * @return void
	 */
	public function incrementProcessed() {
		$this->numProcessed++;
	}
	
	/**
	 * Get the number of processed jobs (Only used to aid testing right now)
	 *
	 * @return void
	 */
	public function getNumProcessed() {
		return $this->numProcessed;
	}

	/**
	 * Register signal handlers that a worker should respond to.
   *
	 */
	private function registerSigHandlers() {
		if(!function_exists('pcntl_signal')) {
			return;
		}
		
		declare(ticks = 1);
		pcntl_signal(SIGTERM, array($this, 'stop'));
		pcntl_signal(SIGINT, array($this, 'stop'));
		pcntl_signal(SIGQUIT, array($this, 'stop'));
		pcntl_signal(SIGUSR2, array($this, 'pauseProcessing'));
		pcntl_signal(SIGCONT, array($this, 'unPauseProcessing'));
		$this->log->lwrite('Registered signals',2);
	}
	
	/**
	 * Stop the worker
	 *
	 * @return void
	 */
	public function stop() {
	  $this->log->lwrite('STOP signal received, stopping processing');
	  $this->stop = true;
	}

	public function pauseProcessing() {
	  $this->log->lwrite('USR2 Signal Received, pausing processing');
	  $this->paused = true;
	}
	
	public function unpauseProcessing() {
	  $this->log->lwrite('SIGCONT Signal Received, unpausing processing');
	  $this->paused = false;
	}
	
}
?>