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

    protected $current_job;
    protected $stop;
    protected $paused;
    protected $queues;
    protected $num_processed = 0;

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
        if (!empty($queues) && is_array($queues)) {
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
    public function reserve_job() {
        $job = BeanstalkerJob::reserve_job($this->queues);
        if ($job) {
            $this->current_job = $job;
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
        $this->register_sig_handlers();
        $this->log->lwrite("Worker started...");
        while (true) {
            if ($this->stop) {
                break;
            }

            if (!$this->paused) {
                $this->reserve_job();
            }

            if (!$this->current_job) {
                if ($interval == 0) {
                    //for testing purposes
                    break;
                }
                //either no job or worker may be paused either way, wait for the interval
                $this->log->lwrite("Waiting $interval seconds...",2);
                usleep($interval * 1000000);
                continue;
            }

            $this->child = $this->fork();

            if ($this->child === 0 || $this->child === false) {
                $status = 'Processing child at ' . strftime('%F %T');
                $this->log->lwrite($status,2);
                $status = $this->perform($this->get_current_job());
                if ($this->child === 0 && !Config::bool('beanstalk.testing')) {
                    exit(0);
                }
            }

            if ($this->child > 0 || Config::bool('beanstalk.testing')) {
                // Parent process, sit and wait
                $status = 'Forked at ' . strftime('%F %T');
                $this->log->lwrite($status,2);
                if (Config::bool('beanstalk.testing')) {
                    $exitStatus = 0;
                }
                else {
                    pcntl_wait($pid_status);
                    $exitStatus = pcntl_wexitstatus($pid_status);
                }
                $this->increment_processed();
                if ($exitStatus !== 0) {
                    $this->release_job();
                    $this->log->lwrite("Bad exit for forked process");
                }
            }
            $this->child = null;
            $this->current_job = NULL;
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
            $job_id = $job->get_job_id();
            $this->reset_unique_id($job_id);
            $this->log->lwrite("Performing Job Id: " . $job_id);
            $result = $job->perform();
            if ($result === true) {
                $this->log->lwrite("Successfully performed Job Id: " . $job_id);
                return true;
            }
            else {
                return false;
            }
        }
        catch (\Exception $e) {
            $this->log->lwrite("Exception: $e");

            $this->release_job($job_id);
            return false;
        }

        return true;
    }

    /**
     * Release the current job back to the queue or delete the job
     *
     * @return void
     */

    private function release_job() {
        $failNum = 5;
        $jobId = $this->current_job->get_job_id();
        $jobStats = $this->statsJob($jobId);
        $releases = (int)$jobStats->__get('releases');
        if (($releases + 1) >= $failNum) {
            $this->current_job->delete_job();
            $this->current_job = null;
            $this->log->lwrite("Deleting Job Id " . $jobId . " failed and released $failNum times");
        }
        else {
            $this->current_job->release_job();
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
        if (Config::bool('beanstalk.testing')) {
            return 0;
        }

        if (!function_exists('pcntl_fork')) {
            return false;
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('Unable to fork child worker.');
        }

        return $pid;
    }

    /**
     * Get the queues this worker is working on
     *
     * @return void
     */
    public function get_queues() {
        return $this->queues;
    }

    /**
     * Get the current job the worker is working on
     *
     * @return BeanstalkerJob
     */
    public function get_current_job() {
        return $this->current_job;
    }

    /**
     * Increment the number of processed jobs (just used for testing)
     *
     * @return void
     */
    public function increment_processed() {
        $this->num_processed++;
    }

    /**
     * Get the number of processed jobs (Only used to aid testing right now)
     *
     * @return void
     */
    public function get_num_processed() {
        return $this->num_processed;
    }

    /**
     * Register signal handlers that a worker should respond to.
   *
     */
    private function register_sig_handlers() {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        declare(ticks = 1);
        pcntl_signal(SIGTERM, array($this, 'stop'));
        pcntl_signal(SIGINT, array($this, 'stop'));
        pcntl_signal(SIGQUIT, array($this, 'stop'));
        pcntl_signal(SIGUSR2, array($this, 'pause_processing'));
        pcntl_signal(SIGCONT, array($this, 'unpause_processing'));
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

    public function pause_processing() {
      $this->log->lwrite('USR2 Signal Received, pausing processing');
      $this->paused = true;
    }

    public function unpause_processing() {
      $this->log->lwrite('SIGCONT Signal Received, unpausing processing');
      $this->paused = false;
    }

    public function reset_unique_id($job_id) {
        $GLOBALS['unique_id'] = $job_id;
    }

}
?>
