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
    public function enqueue($queue, $class_or_method = null, $data, $priority = 1024, $delay = 0) {

        if (empty($queue)) {
            throw new Exception("A valid queue name is required");
        }

        $payload = [];
        if (is_object($data) && method_exists($data, $class_or_method)) {
            $payload["method"] = $class_or_method;
            $payload["data"] = $data;
        }
        else {
            if (class_exists($class_or_method)) {
                $payload["method"] = "perform";
                $payload["class"] = $class_or_method;
                $payload["data"] = $data;
            }
            else {
                throw new Exception("Class does not exist: " . $class_or_method);
            }
        }

        $this->check_data($data);
        $this->set_payload($payload);

        if (Config::bool('beanstalk.disabled')) {
            return $this->perform();
        }

        $this->useTube($queue);

        try {
            $response = $this->put($this->get_encoded_payload(), $priority, $delay);
        }
        catch (\Exception $e) {
            Logging::write($e, 'beanstalker');
            return false;
        }

        return (!is_null($response)) ? $response : false;

    }

    /**
     * checks the payload data
     *
     * @param string $data
     * @return void
     */
    private function check_data($data) {
        if (!is_array($data) && !is_object($data)) {
            throw new Exception( "Data needs to be of type array or object");
            return false;
        }

        if (empty($data)) {
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

    public function set_payload($payload) {
        $this->payload = $payload;
        return true;
    }


    /**
     * Set the Payload with Json Data.
     * string $payload Json encoded string
     */

    public function get_payload_from_json($payload)
    {
        if (is_null(json_decode($payload))) {
            throw new Exception ( "Payload is not json encoded" );
        }
        $decoded = json_decode($payload, true);
        $unserialized = @unserialize($decoded["data"]);
        if ($unserialized !== false || $decoded["data"] === "b:0;") {
            $decoded["data"] = $unserialized;
        }
        return $decoded;
    }

    public function set_payload_from_json($payload)
    {
        if (is_null(json_decode($payload))) {
            throw new Exception ( "Payload is not json encoded" );
        }
        if ($this->payload = $this->get_payload_from_json($payload)) {
            return true;
        }
    }


    /**
     * Get an instance of the worker class to be used
     *
     * @return worker class instance
     */
    private function get_instance() {
        if (array_key_exists("method", $this->payload)) {
            if (is_object($this->payload['data'])) {
                $instance = $this->payload['data'];

                if (method_exists($instance, 'reload')) {
                    $instance->reload();
                }

                if (method_exists($instance, $this->payload["method"])) {
                    return $instance;
                }
            }
        }

        if (!is_null($this->instance)) {
            return $this->instance;
        }

        // make sure that the class can be instantiated and the perform method exists
        if (class_exists($this->payload['class']) && method_exists($this->payload['class'],'perform')) {
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
        $instance = $this->get_instance();
        if ($instance) {
            $method = $this->payload["method"];
            $instance->$method();
        }
        if (!(Config::bool('beanstalk.disabled'))) {
            $this->delete_job();
        }
        return true;
    }

    public function release_job() {
        $this->release($this->watcher);
    }

    /**
     * Reserve a job from the queue
     *
     * @param string $queue
     * @return BeanstalkerJob
     */

    public static function reserve_job($queues) {
        $job = new BeanstalkerJob;
        foreach ($queues as $queue) {
            $job->watch($queue);
        }
        $watcher = $job->reserve(0);
        try{
            if ($watcher instanceof \Pheanstalk_Job) {
                $job->watcher = $watcher;
                $data = $watcher->getData();
                if ($job->set_payload_from_json($data)){
                    return $job;
                }
            }
        }
        catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Delete the job from the queue
     *
     * @return void
     */
    public function delete_job() {
        $this->delete($this->watcher);
    }

    /**
     * Get the Id of this job
     *
     * @return void
     */
    public function get_job_id() {
        return $this->watcher->getId();
    }

    public static function handle_error($errno, $errstr, $errfile, $errline, array $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        Logging::write($errstr, 'BEANSTALKER_JOB');
    }

    /**
     * Get the payload encoded as Json
     *
     * @return void
     */
    public function get_encoded_payload() {
        if (!empty($this->payload)) {
            //used to catch the Invalid UTF-8 issue
            set_error_handler(array('static','handle_error'));

            if (is_object($this->payload["data"])) {
                $this->payload["data"] = serialize($this->payload["data"]);
            }

            $encoded_payload = json_encode($this->payload);
            restore_error_handler();

            return $encoded_payload;
        }
        else {
            return false;
        }
    }
}

?>
