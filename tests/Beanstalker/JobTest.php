<?php

namespace TestApp;

use Pheanstalk,
    Gotron\Config,
    Gotron\Beanstalker\BeanstalkerJob;

require_once dirname(__FILE__) . "/../helpers/jobs/TestingJob.php";

class BeanstalkerJobTests extends UnitTest {

	public $data = array('name' => 'Test', 'number' => 2);

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $config = Config::instance();
        $config->set('beanstalk.disabled', false);
    }

    public static function tearDownAfterClass() {
        parent::setUpBeforeClass();
        self::clear_beanstalk();
    }
	
	public function test_create_new_instance_of_beanstalker_job() {
		$job = new BeanstalkerJob;
		$this->assertInstanceOf('Gotron\Beanstalker\BeanstalkerJob',$job);
	}
    
    public function test_add_job_to_queue() {
        $job = new BeanstalkerJob;
        $jobId = $job->enqueue('SomeQueue', 'TestApp\TestingJob', $this->data);
        $deleteJob = $job->peek($jobId);
        $this->assertInternalType('integer', $jobId);
        $job->delete($deleteJob);
    }
	
    public function test_fails_with_empty_queue() {
        $job = new BeanstalkerJob;
        $this->setExpectedException('Gotron\Exception');
        $job->enqueue('', 'TestApp\TestingJob', $this->data);
    }
    
    public function test_fails_with_non_existent_class() {
        $job = new BeanstalkerJob;
        $this->setExpectedException("Gotron\Exception");
        $job->enqueue('SomeQueue', 'TestApp\BadJob', $this->data);
    }
    
    public function test_fails_with_empty_data() {
        $job = new BeanstalkerJob;
        $this->setExpectedException("Gotron\Exception");
        $job->enqueue('SomeQueue','TestApp\TestingJob', array());
    }
    
    public function test_work_is_performed_properly() {
        $job = new BeanstalkerJob;
        $job->enqueue('SomeQueue', 'TestApp\TestingJob', $this->data);
        $job2 = $job->reserveJob(array('SomeQueue'));
        $this->assertGreaterThan(0, $job2->perform());
    }
}
?>