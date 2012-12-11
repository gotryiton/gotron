<?php

namespace TestApp;

use Gotron\Email\Email,
    Gotron\Email\Services\TestService;

class TestServiceTest extends UnitTest {
    public function setup() {
        $this->email = Email::create("test_email",
            array(
                "subject" => "Hi",
                "to" => "scott@gotryiton.com",
                "reply_to" => "from_test@gotryiton.com",
                "data" => array("message" => "Hello"),
                'view_path' => __DIR__ . "/../../helpers/emails/"
            )
        );
    }

    public function testSendMockEMail() {
        $instance = new TestService;
        $output = $instance->send($this->email);
        $this->assertEquals(true, $output);
    }
}

?>
