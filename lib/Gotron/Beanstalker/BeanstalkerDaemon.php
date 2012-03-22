<?

namespace Gotron\Beanstalker;

declare(ticks = 5);


/**
 * Daemon process for BeanstalkerWorker 
 *
 * @package Gotron\Beanstalker
 */

class BeanstalkerDaemon {
    
    protected $workers = Array();
    protected $numWorkers;
    protected $queues = Array();
    protected $log;
    protected $host = NULL;
    protected $port = NULL;
    
    public function __construct($queues, $numWorkers, $log, $host = null, $port = null)
    {
        $this->queues = $queues;
        $this->numWorkers = $numWorkers;
        $this->log = $log;
        $this->host = $log;
        $this->port = $port;
    }
    
    public function workerAction()
    {
        pcntl_signal(SIGTERM, array($this, 'signalhandler'));

        $child      = false;
        for ($i = 0; $i < $this->numWorkers; $i++) {
            $pid = pcntl_fork();
            if ($pid == 0) {
                $child = true;
                $workerNum = $i;
                break;
            }
            $this->workers[] = $pid;
        }

        if ($child) {
            
            $worker = new BeanstalkerWorker($this->queues);
            if($this->numWorkers == 1){
                $worker->setLog($this->log);
            }
            else{
                $worker->setLog($this->log . "-" . ($workerNum+1));
            }
            
            while ($worker->work());
        } else {
            pcntl_signal(SIGCHLD, array($this, 'signalhandler'));

            while (true) {
                sleep(1);
            }
        }
    }
    
    function signalhandler($signal)
    {
        switch ($signal) {
        case SIGCHLD:
            while (($pid = pcntl_wait($signal, WNOHANG)) > 0) {
                foreach ($workers as $key => $workerpid) {
                    if ($workerpid == $pid) {
                        $newpid = pcntl_fork();
                        if ($newpid == 0) {
                            $workers   = array();
                            $jobscount = 0;
                            return;
                        } else {
                            // $this->logger->info("Restarting worker $pid, new pid $newpid.");
                            $this->workers[$key] = $newpid;
                        }
                    }
                }
            }
            break;
        case SIGTERM:
            foreach ($this->workers as $worker) {
                posix_kill($worker, SIGTERM);
            }
            exit;
        }
    }
    
}
?>