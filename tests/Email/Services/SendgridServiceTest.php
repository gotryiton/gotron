<?php

namespace TestApp;

use Gotron\Email\Email,
    Gotron\Email\Services\SendgridService;

class SendgridServiceTest extends UnitTest {
   public function setup()
    {
        $this->email = Email::create(
            "test_email", 
            array(
                "subject" => "Hi",
                "to" => "scott@gotryiton.com",
                "reply_to" => "from_test@gotryiton.com",
                "data" => array("message" => "Hello"),
                'view_path' => __DIR__ . "/../../helpers/emails/"
            )
        );
    }
    
    public function testSendEmailSuccess() {
        $response = 1;

        $service = $this->getMock('Gotron\Email\Services\SendgridService', array('send_request'));
        $service->expects($this->any())
            ->method('send_request')
            ->will($this->returnValue($response));

        $service_response = $service->send($this->email);
        $this->assertTrue($service_response);
    }
    
    public function testSendEmailFail() {
        $response = 0;

        $service = $this->getMock('Gotron\Email\Services\SendgridService', array('send_request'));
        $service->expects($this->any())
            ->method('send_request')
            ->will($this->returnValue($response));

        $service_response = $service->send($this->email);
        $this->assertFalse($service_response);
    }
    
    public function testBuildMessage() {
        $service = new SendgridService;
        $message = $service->build_message($this->email);
        $this->assertEquals(array($this->email->to => NULL), $message->getTo());
        $this->assertEquals(array($this->email->reply_to => NULL), $message->getReplyTo());
        $this->assertEquals(array($this->email->from => NULL), $message->getFrom());
        $this->assertEquals($this->email->subject, $message->getSubject());
    }
}

?>