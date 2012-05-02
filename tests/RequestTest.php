<?php

namespace TestApp;

use Gotron\Dispatch\Request;

class RequestTest extends UnitTest {

    public function test_load_content_type_and_version() {
        $request = new Request;
        $request->accept_header = "application/v3-json";
        $request->load_content_type_and_version(array());
        $this->assertEquals(3, $request->version);
        $this->assertEquals("application/json", $request->content_type);
    }

    public function test_load_content_type_and_version_no_accept() {
        $request = new Request;
        $request->load_content_type_and_version(array());
        $this->assertEquals(4, $request->version);
        $this->assertEquals("text/html", $request->content_type);
    }

    public function test_load_content_type_and_version_content_type_set() {
        $request = new Request;
        $request->content_type = "application/json";
        $request->load_content_type_and_version(array());
        $this->assertEquals(4, $request->version);
        $this->assertEquals("application/json", $request->content_type);
    }

    public function test_build_request() {
        $request = new Request;
        $request->accept_header = "application/v5-json";
        $request->load_content_type_and_version(array());
        $this->assertEquals(5, $request->version);
        $this->assertEquals("application/json", $request->content_type);
    }
    
}

?>
