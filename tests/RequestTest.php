<?php

namespace TestApp;

use Gotron\Dispatch\Request;

class RequestTest extends UnitTest {

    public function test_load_content_type_and_version_single_versioned_accept() {
        $request = new Request;
        $request->headers = ["Accept" => "application/v3-json"];
        $request->load_content_type_and_version(array());
        $this->assertEquals(3, $request->version);
        $this->assertEquals("application/json", $request->accept_content_type);
    }

    public function test_load_content_type_and_version_versioned_with_multiple_types() {
        $request = new Request;
        $request->headers = ["Accept" => "application/v3-json, text/javascript, */*"];
        $request->load_content_type_and_version(array());
        $this->assertEquals(3, $request->version);
        $this->assertEquals("application/json", $request->accept_content_type);
    }

    public function test_load_content_type_and_version_no_version_with_multiple_types() {
        $request = new Request;
        $request->headers = ["Accept" => "application/json, text/javascript, */*"];
        $request->load_content_type_and_version(array());
        $this->assertEquals(4, $request->version);
        $this->assertEquals("application/json", $request->accept_content_type);
    }

    public function test_load_content_type_and_version_no_accept() {
        $request = new Request;
        $request->load_content_type_and_version(array());
        $this->assertEquals(4, $request->version);
        $this->assertEquals("text/html", $request->accept_content_type);
    }

    public function test_load_content_type_and_version_accept_content_type_set() {
        $request = new Request;
        $request->accept_content_type = "application/json";
        $request->load_content_type_and_version(array());
        $this->assertEquals(4, $request->version);
        $this->assertEquals("application/json", $request->accept_content_type);
    }

    public function test_build_request() {
        $request = new Request;
        $request->headers = ["Accept" => "application/v5-json"];
        $request->load_content_type_and_version(array());
        $this->assertEquals(5, $request->version);
        $this->assertEquals("application/json", $request->accept_content_type);
    }


    public function test_accept_and_content_type_with_charset() {
        $request = new Request;
        $request->headers = ["Accept" => "application/json; charset=UTF-8", "Content-Type" => "application/json; charset=UTF-8", ];
        $request->load_content_type_and_version(array());
        
        $this->assertEquals("json", $request->simple_accept_content_type());
        $this->assertEquals("json", $request->body_content_type());
    }

}

?>
