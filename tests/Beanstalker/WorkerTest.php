<?php

namespace TestApp;

use Gotron\Config,
    Gotron\Beanstalker\BeanstalkerJob,
    Gotron\Beanstalker\BeanstalkerWorker;

require_once __DIR__ . "/../helpers/jobs/TestingJob.php";

class BeanstalkerWorkerTests extends UnitTest {
    public $data = array('name' => 'Test', 'number' => 3);

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $config = Config::instance();
        $config->set('beanstalk.disabled', false);
    }

    public function test_create_new_instance() {
        $worker = new BeanstalkerWorker(array("TestQueue"));
        $this->assertInstanceOf('Gotron\Beanstalker\BeanstalkerWorker',$worker);
    }

    public function test_fails_with_invalid_queue() {
        $this->setExpectedException('Gotron\Exception');
        $worker = new BeanstalkerWorker("");
    }

    public function test_reserve_job() {
        $worker = new BeanstalkerWorker(array("TestQueue"));
        $job = new BeanstalkerJob;
        $jobId = $job->enqueue("TestQueue", "TestApp\TestingJob", $this->data);
        $jobby = $job->peek($jobId);
        $worker->reserve_job();
        $this->assertInstanceOf('Gotron\Beanstalker\BeanstalkerJob', $worker->get_current_job());
    }

    public function test_worker_performs_work() {
        $job = new BeanstalkerJob;
        $job->enqueue('SomeQueue', 'TestApp\TestingJob', $this->data);

        $worker = new BeanstalkerWorker(array("SomeQueue"));
        $worker->setLog('STDOUT');
        $worker->log->do_log = false;
        $worker->work(0);
        $this->assertEquals(1, $worker->get_num_processed());
    }
}

?>
