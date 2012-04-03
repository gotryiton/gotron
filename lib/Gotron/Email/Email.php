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

    public function __construct($type, $to, $options = array()) {

        $this->type = $type;
        $this->to = $to;

        if(isset($options['reply_to'])) {
            $this->reply_to = $options['reply_to'];
        }
        else {
            $this->reply_to = Config::get('email.from');
        }

        $this->from = Config::get('email.from');

        if(isset($options['data']))
            $this->data = $options['data'];

        if(isset($options['view_path'])) {
            $this->view_path = $options['view_path'];
        }
        else {
            $this->view_path = file_join(Config::get('root_directory'), "/app/views/emails/");
        }

        $this->subject = (isset($options['subject'])) ? $options['subject'] : null;
    }
    
    /**
     * Singleton
     *
     */

    public static function get_instance($type, $to, $options = array())
    {
        return new self($type, $to, $options);
    }
    
    /**
     * Create the actual HTML and Text views
     *
     * @return void
     * @author 
     */
    public function create_views()
    {
        $email_view = new EmailView($this);
        $this->html_content = $email_view->html_content();
        $this->text_content = $email_view->text_content();
        if($view_subject = $email_view->get_subject())
            $this->subject = $view_subject;
    }

    public static function create($type, $options)
    {
        $instance = self::get_instance($type, $options['to'], $options);
        $instance->create_views();
        return $instance;
    }

}

?>