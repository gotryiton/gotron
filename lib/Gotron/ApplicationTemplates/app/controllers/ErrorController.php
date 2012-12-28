<?php

namespace @app_namespace;

class ErrorController extends ApplicationController {

    public function maintenance() {
        $this->respond_to([
            'json' => function() {
                $this->error('Maintenance', "@app_namespace is under maintenance.  We'll be back in a few!", ['title' => 'Maintenance']);
            },
            'html' => function() {
                $this->render([], ['view' => 'maintenance']);
            }
        ]);
    }

    public function error_page() {
        $this->respond_to([
            'json' => function() {
                $this->render(['json' => []], ['status' => $this->params['status']]);
            },
            'html' => function() {
                $status = array_key_exists('status', $this->params) ? $this->params['status'] : 500;
                if (array_key_exists('status', $this->params) && in_array($this->params['status'], [404])) {
                    $this->render([], ['view' => $status, 'status' => $status]);
                }
                else {
                    $this->render([], ['view' => 'error', 'status' => $status]);
                }
            }
        ]);
    }

}

?>
