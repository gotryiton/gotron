<?php

namespace TestApp;

use Gotron\Email\Email,
    Gotron\View\EmailView;

class EmailViewTest extends UnitTest
{
    public function setup()
    {
        $this->email = new Email("test_email", "test@gotryiton.com", array("subject" => "Hi", "from" => "from_test@gotryiton.com", "data" => array("message" => "Hello"), 'view_path' => __DIR__ . "/../helpers/emails/"));
        $this->email2 = new Email("test_email_no_subject", "test@gotryiton.com", array("subject" => "Hi", "from" => "from_test@gotryiton.com", "data" => array("message" => "Hello"), 'view_path' => __DIR__ . "/../helpers/emails/"));
    }

    public function testHtmlContent() {
        $email_view = new EmailView($this->email);
        $this->assertEquals("<div>This is a test email</div>\n<div>Hello</div>", $email_view->html_content());
    }

    public function testTextContent() {
        $email_view = new EmailView($this->email);
        $this->assertEquals("This is a test email Hello", $email_view->text_content());
    }
    
    public function testOverridesSubject() {
        $email_view = new EmailView($this->email);
        $this->assertEquals($email_view->get_subject(), "Overriding the subject");
    }
    
    public function testDoesntOverrideSubject() {
        $email_view = new EmailView($this->email2);
        $this->assertEquals($email_view->get_subject(), false);
    }
}

?>