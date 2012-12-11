<?php

namespace Gotron\Email\Services;

/**
 * Email mock service
 *
 */
class TestService extends EmailService {

    public function send($email) {
        return true;
    }

}
?>
