<?php

namespace @app_namespace;

use Gotron\Dispatch\Request;

class HomepageControllerTest extends UnitTest {

    public function testStatus() {
        $response = static::get('/status');
        $body = json_decode($response->body, true);

        $this->assertTrue($body['ok']);
        $this->assertEquals('@app_name', $body['name']);
    }

}

?>
