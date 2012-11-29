<?php

namespace Gotron\View;

use Gotron\Exception;

class TestView extends AbstractView {

    public function generate(array $parameters, $injected_view = null) {
        $this->content = $parameters['text'];
        return $this->content;
    }

    public function get_headers() {
        $this->add_header('Content-type', 'text/test');
        return $this->headers;
    }

}

?>
