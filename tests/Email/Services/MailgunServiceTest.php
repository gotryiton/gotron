<?php

namespace TestApp;

use Gotron\Email\Email,
    Requests_Response,
    Gotron\Email\Services\MailgunService;

class MailgunServiceTest extends UnitTest {
    public function setup() {
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

    public function testSendEmail() {
        $response = new Requests_Response;
        $response->status_code = 200;
        $response->body = '{
          "message": "Queued. Thank you",
          "id": "<20120215192122.7115.62259@gotryiton.mailgun.org>"
        }';
        $response->success = true;

        $service = $this->getMock('Gotron\Email\Services\MailgunService', array('send_request'));
        $service->expects($this->any())
             ->method('send_request')
             ->will($this->returnValue($response));

        $service_response = $service->send($this->email);
        $this->assertTrue($service_response);
    }

    public function testSendEmailFail() {
        $response = new Requests_Response;
        $response->status_code = 200;
        $response->body = '{
          "message": "Failure."
        }';
        $response->success = false;

        $service = $this->getMock('Gotron\Email\Services\MailgunService', array('send_request'));
        $service->expects($this->any())
             ->method('send_request')
             ->will($this->returnValue($response));

        $service_response = $service->send($this->email);
        $this->assertFalse($service_response);
    }
}

?>
