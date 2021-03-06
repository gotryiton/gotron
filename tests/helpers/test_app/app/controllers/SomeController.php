<?php

namespace TestApp;

use Gotron\Controller;

class SomeController extends Controller {

    protected $before_filter = array('before_test' => array('filter_test'));
    protected $after_filter = array('after_test' => array('filter_test'));

    public $before_test_variable = 1000;
    public $after_test_variable = 9999;

    public function index() {
        $data = array("name" => $this->params['name']);
        $this->render(array('json' => $data));
    }

    public function test_json() {
        $data = array("name" => $this->params['name']);
        $this->render(array('json' => $data));
    }

    public function test_respond_to() {
        $data = array("name" => $this->params['name']);
        $this->respond_to([
            'html' => function() use($data){
                $this->render($data, array('view' => 'test', 'layout' => false));
            },
            'json' => function() use($data) {
                $this->render(array('json' => $data));
            }
        ]);
    }

    public function test_respond_to_version() {
        $data = array("name" => $this->params['name']);
        $this->respond_to([
            'html' => function() use($data){
                $this->render($data, array('view' => 'test', 'layout' => false));
            },
            'json' => [
                '4.0' => function() {
                    $this->render(['json' => ['name' => '4.0.0']]);
                },
                '4.0.1' => function() {
                    $this->render(['json' => ['name' => '4.0.1']]);
                },
                '3.0.0' => function() {
                    $this->render(['json' => ['name' => '3.0']]);
                }
            ]
        ]);
    }

    public function test_respond_to_version_presenter() {
        $data = array("name" => $this->params['name']);
        $this->respond_to([
            'html' => function() use($data){
                $this->render($data, array('view' => 'test', 'layout' => false));
            },
            'json' => [
                '4.0.0' => function() {
                    $this->render(['json' => VersionPresenter::to_array([])]);
                },
                '4.0.1' => function() {
                    $this->render(['json' => VersionPresenter::to_array([])]);
                },
                '3.0.0' => function() {
                    $this->render(['json' => SomeVersionPresenter::to_array([])]);
                }
            ]
        ]);
    }

    public function test_respond_to_version_multi_presenter() {
        $data = array("name" => $this->params['name']);
        $this->respond_to([
            'html' => function() use($data){
                $this->render($data, array('view' => 'test', 'layout' => false));
            },
            'json' => function() {
                $this->render(['json' => MultiVersionPresenter::to_array([])]);
            }
        ]);
    }

    public function test_php_no_layout() {
        $data = array("name" => $this->params['name']);
        $this->render($data, array('view' => 'test', 'layout' => false));
    }

    public function test_php_layout_default() {
        $data = array("name" => $this->params['name']);
        $this->render($data, array('view' => 'test'));
    }

    public function test_php_layout_set() {
        $data = array("name" => $this->params['name']);
        $this->render($data, array('view' => 'test', 'layout' => 'layout_set'));
    }

    public function test_route() {
        $this->render(array('json' => array('test' => 123456)));
    }

    public function test_named() {
        $this->render(array('json' => array('test' => (int)$this->params['named'])));
    }

    public function test_named_string_param() {
        $this->render(array('json' => array('test' => $this->params['named'])));
    }

    public function test_named_two() {
        $this->render(array('json' => array('test' => (int)$this->params['named'], 'test_two' => (int)$this->params['named_two'])));
    }

    public function test_array() {
        $this->render(array('json' => array('test' => (int)$this->params['array_params'][0], 'test_two' => (int)$this->params['array_params'][1], 'test_three' => (int)$this->params['array_params'][2], 'test_four' => (int)$this->params['array_params'][3])));
    }

    public function test_custom() {
        $this->render(array('json' => array('test' => (int)$this->params['custom'])));
    }

    public function test_bool() {
        $this->render(array('json' => array('test' => $this->params['test_bool'])));
    }

    public function test_query() {
        $this->render(array('json' => array('test' => $this->params['test_query_param'])));
    }

    public function test_custom_only() {
        $this->render(array('json' => array('test_custom' => (int)$this->params['custom'])));
    }

    public function filter_test() {
        $data = array("name" => $this->params['name']);
        $this->render(array('json' => $data));
    }

    public function optional_named() {
        $data = array("name" => (int)$this->params['optional_named_parameter']);
        $this->render(array('json' => $data));
    }

    public function test_etag_caching() {
        if ($this->stale("something")) {
            $data = array("text" => "Etag cache was not found");
            $this->render(array('json' => $data));
        }
    }

    protected function before_test() {
        $this->before_test_variable = 999999;
    }

    protected function after_test() {
        $this->after_test_variable = 111111;
    }
}

?>
