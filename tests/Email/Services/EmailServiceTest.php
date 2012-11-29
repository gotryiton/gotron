<?php

namespace TestApp;

use Gotron\Email\Services\EmailService;

class EmailServiceTest extends UnitTest {

    public function testLoadMailgunService() {
        $instance = EmailService::instance("mailgun");
        $this->assertInstanceOf("Gotron\\Email\\Services\\MailgunService", $instance);
    }

    public function testLoadTestService() {
        $instance = EmailService::instance("test");
        $this->assertInstanceOf("Gotron\\Email\\Services\\TestService", $instance);
    }

    public function testLoadSendgridService() {
        $instance = EmailService::instance("sendgrid");
        $this->assertInstanceOf("Gotron\\Email\\Services\\SendgridService", $instance);
    }

    public function testFailLoadInvalidService() {
        $this->setExpectedException("Gotron\Exception", "FailService not found!");
        $instance = EmailService::instance("fail");
    }

}

?>
