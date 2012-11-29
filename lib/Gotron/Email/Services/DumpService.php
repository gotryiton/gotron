<?php

namespace Gotron\Email\Services;

use Gotron\Config,
    Gotron\Logging;

/**
 * Mailgun email service
 *
 */
class DumpService extends EmailService {

    const DUMP_DIRECTORY = "public/emails";

    public function send($email) {
        return $this->save($email);
    }

    protected function save($email) {
        $directory = (Config::bool('dump_email.directory')) ? Config::get('dump_email.directory') : self::DUMP_DIRECTORY;
        $time = time();
        $filename_html = file_join(Config::get('root_directory'), $directory, "{$email->type}_{$time}.html");
        $filename_txt = file_join(Config::get('root_directory'), $directory, "{$email->type}_{$time}.txt");
        if (file_put_contents($filename_html, $email->html_content) !== false) {
            if (file_put_contents($filename_txt, $email->text_content) !== false) {
                return true;
            }
        }
        return false;
    }

}
?>
