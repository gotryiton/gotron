<?

namespace Gotron\Beanstalker;

use Gotron\Config,
    Gotron\Exception,
    Gotron\Jobs,
    Gotron\Logging;

/**
 * BeanstalkerJob
 *
 * @package Gotron\Beanstalker
 */

class BeanstalkerJob extends Beanstalker {
  
    //Data sent with the Job
	public $payload;
	
	//Instance of Job called from payload
	private $instance;
	
	//Pheanstalk_Job watching a queue
	private $watcher;
	
	
	/**
	 * Queue the data for work to be performed
	 * @param string $queue, 
	 * @param string $class, the worker class to be used
	 * @param Array/Object $data, the data provided to the worker class
	 * @param $priority, priority of job (default 1024, 0 = most urgent)
	 */
	public function enqueue($queue, $class = null, $data, $priority = 1024, $delay=0) {

        if(empty($queue)) {
            throw new Exception("A valid queue name is required");
        }

        if(!class_exists($class)) {
            throw new Exception( "Class does not exist: " . $class );
        }

		$this->checkData($data);
		
		$this->setPayload($class, $data);

		if (Config::bool('beanstalk.disabled')) {
			return $this->perform();
		}
		else{
			$this->useTube($queue);

			$response = $this->put($this->getEncodedPayload(),$priority, $delay);
			
			return (!is_null($response)) ? $response : false;	
		}
		
	}
	
	/**
	 * checks the payload data
	 *
	 * @param string $data 
	 * @return void
	 */
	private function checkData($data)
	{
		if(!is_array($data)) {
			throw new Exception( "Data needs to be of type array");
			return false;
		}
		
		if(empty($data)) {
			throw new Exception( "Data cannot be empty");
			return false;
		}
		
		return true;
	}
	
	/**
	 * Set the Payload from Class and Array or Object
	 * String $class for worker class to be used in job
	 * Array or Object $data Json encoded string
	 * @author Scott Bader
	 */
		
	public function setPayload($class,$data)
	{
		$this->payload = array("class" => $class, "data" => $data);
		return true;
	}
	
	
	/**
	 * Set the Payload with Json Data.
	 * string $payload Json encoded string
	 */
	
	public function getPayloadFromJson($payload)
	{
		if(is_null(json_decode($payload))) {
			throw new Exception ( "Payload is not json encoded" );
		}
 		return json_decode($payload,true);
	}
	
	public function setPayloadFromJson($payload)
	{
		if(is_null(json_decode($payload))) {
			throw new Exception ( "Payload is not json encoded" );
		}
		if($this->payload = $this->getPayloadFromJson($payload)) {
			return true;
		}
	}
	
	
	/**
	 * Get an instance of the worker class to be used
	 *
	 * @return worker class instance
	 */
	private function getInstance()
	{
		if(!is_null($this->instance)) {
			return $this->instance;
		}
		
		// make sure that the class can be instantiated and the perform method exists
		if(class_exists($this->payload['class']) && method_exists($this->payload['class'],'perform')) {
			$this->instance = new $this->payload['class'];
			$this->instance->job = $this;
			$this->instance->data = $this->payload['data'];
			return $this->instance;
		}
	}
	
	/**
	 * Performs the job with the worker class and then deletes it from the queue
	 * returns true or exception message
	 */
	public function perform() {
		$instance = $this->getInstance();
		try {
			if($instance){
				$instance->perform();
			}
		}
		catch(Exception $e) {
            //release the job back to the queue
            Logging::write($e, 'beanstalker');
            return $e;
		}
   		if (!(Config::bool('beanstalk.disabled'))) {
            $this->deleteJob();
        }
        return true;
	}
	
  public function releaseJob() {
    $this->release($this->watcher);
  }
	
	/**
	 * Reserve a job from the queue
	 *
	 * @param string $queue
	 * @return BeanstalkerJob
	 */
	 
	public static function reserveJob($queues) {
		$job = new BeanstalkerJob;
		foreach($queues as $queue) {
            $job->watch($queue);
		}
		$watcher = $job->reserve(0);
		try{
			if($watcher instanceof \Pheanstalk_Job) {
				$job->watcher = $watcher;
				$data = $watcher->getData();
				if($job->setPayloadFromJson($data)){
					return $job;
				}
			}
		}
		catch(Exception $e) {
			return false;
		}
		return false;
	}
	
	/**
	 * Delete the job from the queue
	 *
	 * @return void
	 */
	public function deleteJob()
	{
		$this->delete($this->watcher);
	}
	
	/**
	 * Get the Id of this job
	 *
	 * @return void
	 */
	public function getJobId()
	{
	  return $this->watcher->getId();
	}
	
	public static function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        \Logging::write($errstr,'BEANSTALKER_JOB');
    }

	/**
	 * Get the payload encoded as Json
	 *
	 * @return void
	 */
	public function getEncodedPayload()
	{
		if(!empty($this->payload))
		{
            //used to catch the Invalid UTF-8 issue
		    set_error_handler(array('static','handleError'));

			$encoded_payload = json_encode($this->payload);
            restore_error_handler();

            return $encoded_payload;
		}
		else{
			return false;
		}
	}
}


?>