<?php

namespace Gotron\Email\Services;

use Requests,
    Gotron\Config,
    Gotron\Logging;

/**
 * Mailgun email service
 *
 */
class MailgunService extends EmailService {

    const API_DOMAIN = "http://gtio.mailgun.org/";
    const API_KEY = "12345667";
    const API_BASE = "https://api.mailgun.net/v2";

	public function send($email) {
        Logging::write("Sending email with mailgun", 'EMAILTESTING');

        $response = $this->send_request($email);

        Logging::write("Mailgun response: " . json_encode(['status_code' => $response->status_code, 'body' => $response->body, 'success' => $response->success]), 'EMAILTESTING');

        return ($response->success === true);
	}

	protected function send_request($email) {
        $domain = (Config::bool('mailgun.domain')) ? Config::get('mailgun.domain') : self::API_DOMAIN;
        $key = (Config::bool('mailgun.api_key')) ? Config::get('mailgun.api_key') : self::API_KEY;

	    $request_url = self::API_BASE . "/" . $domain . "/messages";
	    $params = $this->build_request($email);
	    $options = array('auth' => array('api', $key));
        $response = Requests::post($request_url, array(), $params, $options);

        return $response;
	}

	public function build_request($email) {
	    return array(
                'from' => $email->from,
                'to' => $email->to,
    	        'subject' => $email->subject,
    	        'text' => $email->text_content,
    	        'html' => $email->html_content,
	        );
	}
	
}
?>
