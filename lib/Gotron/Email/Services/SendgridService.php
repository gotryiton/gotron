<?php

namespace Gotron\Email\Services;

use Swift_Message,
    Swift_Mailer,
    Swift_SmtpTransport,
    Gotron\Config;

require_once __DIR__ . '/../../../../vendor/swiftmailer/lib/swift_init.php';

/**
 * Sendgrid email service
 *
 */
 
class SendgridService extends EmailService
{
    const USER = "SENDGRID_USER";
    const PASSWORD = "SENDGRID_PASSWORD";
    const SERVER = "SENDGRID_SERVER";
    const PORT = "SENDGRID_PORT";

    protected $swift = null;

	public function send($email) {
	    $response = $this->send_request($email);
	    if($response === 1)
	        return true;
	    else
	        return false;
	}
	
	/**
	 * Sends the request to sendgrid
	 *
	 * @param Email $email 
	 * @return int Number of successful messages
	 */
	protected function send_request($email)
	{
	    $message = $this->build_message($email);
		return $this->get_sendgrid_connection()->send($message);
	}
	
	/**
	 * Create the swift_message to be sent
	 *
	 * @param Email $email 
	 * @return void
	 */
	public function build_message($email)
	{
	    $message = new Swift_Message;
	    $message->setReplyTo($email->reply_to);
	    $message->setFrom($email->from);
        $message->setTo($email->to);
        $message->setSubject($email->subject);
		$message->setBody($email->html_content, 'text/html');
		$message->addPart($email->text_content, 'text/plain');

        $headers = $message->getHeaders();
        $headers->addTextHeader('X-SMTPAPI', $this->build_headers($email));

		return $message;
	}

    public function build_headers($email) {
        $api_header = new SmtpApiHeader;
        $site_type = defined('SITE_TYPE') ? SITE_TYPE : "development";
        $category = $site_type . '-' . $email->type;
        $api_header->setCategory($category);
        return $api_header->asJSON();
    }
	
	/**
	 * Gets the SMTP connection
	 *
	 * @return SwiftMailer
	 */
	private function get_sendgrid_connection()
	{
	    if($this->swift instanceof Swift_Mailer){
	        return $this->swift;
        }
        else {
            $config = Config::instance();
            $config = $config['sendgrid'];

            $server = isset($config['server']) ? $config['server'] : self::SERVER;
            $port = isset($config['port']) ? $config['port'] : self::PORT;
            $user = isset($config['user']) ? $config['user'] : self::USER;
            $password = isset($config['password']) ? $config['password'] : self::PASSWORD;

            $this->transport = Swift_SmtpTransport::newInstance($server, $port);
            $this->transport->setTimeout(15);
            $this->transport->setUsername($user);
            $this->transport->setPassword($password);
            return $this->swift = Swift_Mailer::newInstance($this->transport);
        }
	}
	
}
?>
