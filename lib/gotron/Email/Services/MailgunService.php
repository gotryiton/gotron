<?php

namespace Gotron\Email\Services;

/**
 * Mailgun email service
 *
 */
class MailgunService extends AbstractEmailService
{

    private $api_domain = "http://gtio.mailgun.org/";
    private $api_key = "12345667";
    private $api_user = "api";
    const API_BASE = "https://api.mailgun.net/v2";

	public function send($email)
	{
        $response = $this->send_request($email);
        return ($response->success === true);
	}

	protected function send_request($email)
	{

	    $request_url = self::API_BASE . "/" . $this->api_domain . "/messages";
	    
	    $params = $this->build_request($email);

	    $options = array('auth' => array('api', $this->api_key));

        $response = Requests::post($request_url, array(), $params, $options);

        return $response;

	}
	
	public function build_request($email)
	{
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
