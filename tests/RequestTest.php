<?php

namespace TestApp;

use Gotron\Request;

class RequestTest extends UnitTest {

    public function test_load_content_type_and_version() {
        $_SERVER['HTTP_ACCEPT'] = "application/v3-json";
        $request = new Request;
        $request->load_content_type_and_version();
        $this->assertEquals(3, $request->version);
        $this->assertEquals("application/json", $request->content_type);
    }

    public function test_load_content_type_and_version_no_accept() {
        unset($_SERVER['HTTP_ACCEPT']);
        $request = new Request;
        $request->load_content_type_and_version();
        $this->assertEquals(4, $request->version);
        $this->assertEquals("text/html", $request->content_type);
    }

    public function test_build_request() {
        $_SERVER['HTTP_ACCEPT'] = "application/v5-json";
        $request = new Request;
        $request->load_content_type_and_version();
        $this->assertEquals(5, $request->version);
        $this->assertEquals("application/json", $request->content_type);
    }
    
}

?>
