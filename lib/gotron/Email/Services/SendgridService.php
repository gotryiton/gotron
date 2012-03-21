<?php

namespace Gotron\Email\Services;

use Swift_Message;

require_once __DIR__ . '/../../../vendor/Swift/swift_init.php';

/**
 * Sendgrid email service
 *
 */
 
class SendgridService extends AbstractEmailService
{
    const USER = "SENDGRID_USER";
    const PASSWORD = "SENDGRID_PASSWORD";
    const SERVER = "SENDGRID_SERVER";
    const PORT = "SENDGRID_PORT";

	public function send($email)
	{
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
	    $message = new \Swift_Message;
	    $message->setReplyTo($email->reply_to);
	    $message->setFrom($email->from);
        $message->setTo($email->to);
        $message->setSubject($email->subject);
		$message->setBody($email->html_content, 'text/html');
		$message->addPart($email->text_content, 'text/plain');
		return $message;
	}
	
	/**
	 * Gets the SMTP connection
	 *
	 * @return SwiftMailer
	 */
	private function get_sendgrid_connection()
	{
	    if(!is_null($this->swift)){
	        return $this->swift;
        }
        else{
            $this->emailHeader = new \SmtpApiHeader();
            $this->transport = \Swift_SmtpTransport::newInstance(self::SERVER, self::PORT);
            $this->transport->setTimeout(15);
            $this->transport->setUsername(self::USER);
            $this->transport->setPassword(self::PASSWORD);
            return $this->swift = \Swift_Mailer::newInstance($this->transport);
        }
	}
	
}
?>
