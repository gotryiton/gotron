<?php

namespace TestApp;

use Gotron\Email\Services\AbstractEmailService;

class AbstractEmailServiceTest extends UnitTest {
    
    public function testLoadMailgunService() {
        $instance = AbstractEmailService::instance("mailgun");
        $this->assertInstanceOf("Gotron\\Email\\Services\\MailgunService", $instance);
    }
    
    public function testLoadTestService() {
        $instance = AbstractEmailService::instance("test");
        $this->assertInstanceOf("Gotron\\Email\\Services\\TestService", $instance);
    }
    
    public function testLoadSendgridService() {
        $instance = AbstractEmailService::instance("sendgrid");
        $this->assertInstanceOf("Gotron\\Email\\Services\\SendgridService", $instance);
    }
    
    public function testFailLoadInvalidService() {
        $this->setExpectedException("Gotron\Exception", "FailService not found!");
        $instance = AbstractEmailService::instance("fail");
    }
    
}

?>