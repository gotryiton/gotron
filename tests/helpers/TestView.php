<?php

namespace Gotron\View;

use Gotron\Exception;

class TestView extends AbstractView {

    public function generate(array $parameters) {
        $this->content = $parameters['text'];
        return $this->content;
    }

    protected function get_headers() {
        $this->add_header('Content-type', 'text/test');
		return $this->headers;
    }

}

?>