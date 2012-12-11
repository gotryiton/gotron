<?php

namespace Gotron\Email;

use Gotron\Config,
    Gotron\View\EmailView;

/**
 * An email to be sent through a service
 *
 * @package Gotron;
 */
class Email {

    /**
     * The HTML content of the email
     * @var string
     */
    public $html_content;

    /**
     * The text content of the email.
     *
     * @var string
     */
    public $text_content;

    /**
     * Subject of the email
     *
     * @var string
     */
    public $subject;

    /**
     * The recipient of the email
     *
     * @var string
     */
    public $to;

    /**
     * The reply to address on the email
     *
     * @var string
     */
    public $reply_to;

    /**
     * The from email address
     *
     * @var string
     */
    public $from;

    /**
     * The name of this email to pass to the View
     *
     * @var string
     */
    public $type;
    /**
     * The data to go into the Email View
     *
     * @var array
     */

    public $data = array();

    public $view_path = null;

    public $layout = "email";

    public function __construct($type, $to, $options = array()) {

        $this->type = $type;

        if (Config::bool('email.testing')) {
            $this->to = Config::get('email.test_recipient');
        }
        else {
            $this->to = $to;
        }

        if (isset($options['reply_to'])) {
            $this->reply_to = $options['reply_to'];
        }
        else {
            $this->reply_to = Config::get('email.from');
        }

        $this->from = Config::get('email.from');

        if (isset($options['data']))
            $this->data = $options['data'];

        if (isset($options['view_path'])) {
            $this->view_path = $options['view_path'];
        }
        else {
            $this->view_path = file_join(Config::get('root_directory'), "/app/views/emails/");
        }

        if (isset($options['layout'])) {
            $this->layout = $options['layout'];
        }

        $this->subject = (isset($options['subject'])) ? $options['subject'] : null;
    }

    /**
     * Singleton
     *
     */

    public static function get_instance($type, $to, $options = array()) {
        return new self($type, $to, $options);
    }

    /**
     * Create the actual HTML and Text views
     *
     * @return void
     * @author
     */
    public function create_views() {
        $view_path = file_join($this->view_path, "{$this->type}.php");
        $view = EmailView::render($this->data, $view_path, null);

        $this->subject = (!empty($view->subject)) ? $view->subject : $this->subject;

        if ($this->layout) {
           $view = EmailView::render($this->data, $this->get_layout_path(), null, $view);
        }

        $this->html_content = $view->content;
        $this->text_content = $view->text_content();
    }

    public static function create($type, $options) {
        $instance = self::get_instance($type, $options['to'], $options);
        $instance->create_views();

        return $instance;
    }

    protected function get_layout_path() {
        return realpath(file_join(Config::get('root_directory'), Config::get('view_directory'), "layouts", "{$this->layout}.php"));
    }

}

?>
