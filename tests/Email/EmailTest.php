<?php

namespace TestApp;

use Gotron\Email\Email;

class EmailTest extends UnitTest {

    public function testCreateEmailNoLayout() {
        $options = array(
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/",
            'layout' => false
        );

        $email_type = "test_email";
        
        $email = Email::create($email_type, $options);
        
        $this->assertEquals($email_type, $email->type);
        $this->assertEquals("Overriding the subject", $email->subject);
        $this->assertEquals($options['to'], $email->to);
        $this->assertEquals($options['reply_to'], $email->reply_to);
        $this->assertEquals($options['data'], $email->data);
        $this->assertEquals("<div>This is a test email</div>\n<div>Hello</div>", $email->html_content);
        $this->assertEquals("This is a test email Hello", $email->text_content);
    }

    public function testCreateEmailDefaultLayout() {
        $options = array(
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/"
        );

        $email_type = "test_email";
        
        $email = Email::create($email_type, $options);
        
        $this->assertEquals($email_type, $email->type);
        $this->assertEquals("Overriding the subject", $email->subject);
        $this->assertEquals($options['to'], $email->to);
        $this->assertEquals($options['reply_to'], $email->reply_to);
        $this->assertEquals($options['data'], $email->data);
        $this->assertEquals("This is the start of a test email layout\n<div>This is a test email</div>\n<div>Hello</div> \nThis is the end of a test email layout", $email->html_content);
        $this->assertEquals("This is the start of a test email layout This is a test email Hello\nThis is the end of a test email layout", $email->text_content);
    }

    public function testCreateEmailOverrideLayout() {
        $options = array(
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/",
            'layout' => 'override_email'
        );

        $email_type = "test_email";

        $email = Email::create($email_type, $options);

        $this->assertEquals($email_type, $email->type);
        $this->assertEquals("Overriding the subject", $email->subject);
        $this->assertEquals($options['to'], $email->to);
        $this->assertEquals($options['reply_to'], $email->reply_to);
        $this->assertEquals($options['data'], $email->data);
        $this->assertEquals("This is the start of a second test email layout\n<div>This is a test email</div>\n<div>Hello</div> \nThis is the end of a second test email layout", $email->html_content);
        $this->assertEquals("This is the start of a second test email layout This is a test email\nHello This is the end of a second test email layout", $email->text_content);
    }

    public function testCreateEmailViewOverridingSubject() {
        $options = array(
            'subject' => 'This is a test email',
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/",
            'layout' => false
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

    public function testCreateEmailWithSubjectPrefix() {
        $options = array(
            'to' => 'Test Person <test@gotryiton.com>',
            'reply_to' => 'Test GTIO <reply@gotryiton.com>',
            'data' => array("message" => "Hello"),
            'view_path' => __DIR__ . "/../helpers/emails/",
            'layout' => 'override_email',
            'subject_prefix' => 'BLAM'
        );

        $email_type = "test_email";

        $email = Email::create($email_type, $options);

        $this->assertEquals($email_type, $email->type);
        $this->assertEquals("BLAM Overriding the subject", $email->subject);
        $this->assertEquals($options['to'], $email->to);
        $this->assertEquals($options['reply_to'], $email->reply_to);
        $this->assertEquals($options['data'], $email->data);
        $this->assertEquals("This is the start of a second test email layout\n<div>This is a test email</div>\n<div>Hello</div> \nThis is the end of a second test email layout", $email->html_content);
        $this->assertEquals("This is the start of a second test email layout This is a test email\nHello This is the end of a second test email layout", $email->text_content);
    }

}

?>