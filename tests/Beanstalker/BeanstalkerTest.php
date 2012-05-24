<?php

namespace TestApp;

use Gotron\Beanstalker\BeanstalkerJob,
    Gotron\Beanstalker\BeanstalkerWorker,
    Gotron\Config,
    ActiveRecord\ConnectionManager,
    GTIOUnit\UnitDB\Fixture,
    GTIOUnit\UnitDB\Utils,
    Gotron\Cache;

require_once dirname(__FILE__) . "/../helpers/jobs/UnitTestJob.php";
require_once dirname(__FILE__) . "/../helpers/jobs/UnitTestErrorJob.php";

class BeanstalkerTests extends UnitTest {
    public $queueName = 'UnitTestQueue';
    public $className = 'TestApp\UnitTestJob';
    public $log;
    public $log_file;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        static::clear_beanstalk();
    }
    
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
    }
    
    public function setUp() {
        $this->log = dirname(__FILE__) . '/../helpers/jobs/jobtest-log';
        $this->log_file = $this->log . "_" . date('Y-m-d') . '.log';
        
        parent::setUp();
    }
    
    public function tearDown() {
        parent::tearDown();
    }

    public function test_enqueue_and_perform_work() {
        $config = Config::instance();
        $config->set('beanstalk.disabled', false);
        $job = new BeanstalkerJob;
        $result = $job->enqueue($this->queueName, $this->className, array('name' => 'UnitTestName'));
        $this->assertInternalType('integer', $result);
        

        $this->expectOutputString("[test] Worker started...\n[test] Performing Job Id: $result\nThis is the output from UnitTestName\n[test] Successfully performed Job Id: $result\n[test] Worker stopped...\n");
        $worker = new BeanstalkerWorker(array($this->queueName));
        $worker->setLog("STDOUT");
        $worker->work(0);
    }

    public function test_enqueue_invalid_job() {
        $job = new BeanstalkerJob;
        
        $this->setExpectedException('Gotron\Exception');
        
        $result = $job->enqueue($this->queueName,'UnitTestInvalidJob',array('name' => 'UnitTestName'));
    }
    
    public function test_logs_exception_in_job() {
        $job = new BeanstalkerJob;
        $result = $job->enqueue('ErrorQueue', 'TestApp\UnitTestErrorJob', array('name' => 'UnitTestName'));
        $this->expectOutputRegex("/\[test\] Exception: exception 'Gotron\\\Exception' with message 'Testing an exception'/");
        $worker = new BeanstalkerWorker(array('ErrorQueue'));
        $worker->setLog("STDOUT");
        $worker->work(0);
    }

    public function test_model_queue_callback() {
        $config = Config::instance();
        $config->set('beanstalk.disabled', false);
        $config->set('model_directory', 'tests/GTIO/helpers/models');
        Utils::clear_db($config['database']);

        $connection = ConnectionManager::get_connection();
        $connection->query(Book::$create_query);
        $connection->query(Publisher::$create_query);

        $fix = new Fixture(__DIR__ . "/../fixtures/");
        $fix->create('publisher');

        $book = Book::create(array(
                "title" => "ORIGINAL TITLE",
                "author" => "Some author",
                "publisher_id" => 1
            ));

        $this->expectOutputRegex("/\[test\] Worker started\.\.\.\n\[test\] Performing Job Id: \d+\n\[test\] Successfully performed Job Id\: \d+\n\[test\] Worker stopped\.\.\./");

        $worker = new BeanstalkerWorker(array("CallbackDelayedJobQueue"));
        $worker->setLog("STDOUT");
        $worker->work(0);

        $book->reload();

        $this->assertEquals("CHANGED TITLE", $book->title);

        $connection = ConnectionManager::get_connection();
        $connection->query("DROP TABLE IF EXISTS books");
        $connection->query("DROP TABLE IF EXISTS publishers");
    }
}
  
?>