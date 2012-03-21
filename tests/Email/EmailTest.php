<?php

namespace TestApp;

use Gotron\Email\Email;

class EmailTest extends UnitTest {

    public function testCreateEmail() {
        $options = array(
            'subject' => 'Overriding the subject',
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/"
        );

        $email_type = "test_email";
        
        $email = Email::create($email_type, $options);
        
        $this->assertEquals($email_type, $email->type);
        $this->assertEquals($options['subject'], $email->subject);
        $this->assertEquals($options['to'], $email->to);
        $this->assertEquals($options['reply_to'], $email->reply_to);
        $this->assertEquals($options['data'], $email->data);
        $this->assertEquals("<div>This is a test email</div>\n<div>Hello</div>", $email->html_content);
        $this->assertEquals("This is a test email Hello", $email->text_content);
    }

    public function testCreateEmailViewNoSubject() {
        $options = array(
            'subject' => 'This is a test email',
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/"
        );
        
        $email_type = "test_email_no_subject";
        
        $email = Email::create($email_type, $options);
        
        $this->assertEquals($email_type, $email->type);
        $this->assertEquals($options['subject'], $email->subject);
        $this->assertEquals($options['to'], $email->to);
        $this->assertEquals($options['reply_to'], $email->reply_to);
        $this->assertEquals($options['data'], $email->data);
        $this->assertEquals("<div>This is a test email</div>\n<div>Hello</div>", $email->html_content);
        $this->assertEquals("This is a test email Hello", $email->text_content);
    }

}

?>