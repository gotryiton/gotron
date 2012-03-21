<?php

namespace Gotron\Email\Services;

/**
 * Email mock service
 *
 */
class TestService extends AbstractEmailService {

	public function send($email)
	{
	    return true;
	}
	
}
?>
