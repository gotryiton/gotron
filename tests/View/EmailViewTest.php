<?php

namespace TestApp;

use Gotron\Email\Email,
    Gotron\View\EmailView;

class EmailViewTest extends UnitTest
{
    public function setup() {
        $this->email = new Email("test_email", "test@gotryiton.com", array("subject" => "Hi", "from" => "from_test@gotryiton.com", "data" => array("message" => "Hello"), 'view_path' => __DIR__ . "/../helpers/emails/"));
        $this->email2 = new Email("test_email_no_subject", "test@gotryiton.com", array("subject" => "Hi", "from" => "from_test@gotryiton.com", "data" => array("message" => "Hello"), 'view_path' => __DIR__ . "/../helpers/emails/"));
    }

    public function testHtmlContent() {
        $view = EmailView::render($this->email->data, file_join($this->email->view_path, $this->email->type . ".php"));
        $this->assertEquals("<div>This is a test email</div>\n<div>Hello</div>", $view->content);
    }

    public function testTextContent() {
        $view = EmailView::render($this->email->data, file_join($this->email->view_path, $this->email->type . ".php"));
        $this->assertEquals("This is a test email Hello", $view->text_content());
    }

    public function testOverridesSubject() {
        $view = EmailView::render($this->email->data, file_join($this->email->view_path, $this->email->type . ".php"));
        $this->assertEquals($view->get_subject(), "Overriding the subject");
    }

    public function testDoesntOverrideSubject() {
        $view = EmailView::render($this->email2->data, file_join($this->email2->view_path, $this->email2->type . ".php"));
        $this->assertEquals($view->get_subject(), false);
    }
}

?>
