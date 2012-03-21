<?php

namespace TestApp;

use Gotron\Beanstalker\Beanstalker;

class BeanstalkerConnectionTest extends UnitTest {

    public function test_create_new_instance_of_beanstalker() {
        $bean = new Beanstalker;
        $this->assertInstanceOf('Gotron\Beanstalker\Beanstalker', $bean);
    }
	
	public function test_connection_to_beanstalk() {
		$bean = new Beanstalker;
		$this->assertInternalType('array', $bean->listTubes());
	}

}
?>