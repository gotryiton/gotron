<?php

namespace @app_namespace;

class HomepageController extends ApplicationController {

    public function index() {
        $this->respond_to([
            'html' => function() {
                $this->render(['name' => '@app_namespace'], ['view' => 'index']);
            },
            'json' => function() {
                $this->render(['json' => StatusPresenter::to_array(['ok' => true, 'name' => '@app_name'])]);
            }
        ]);
    }

    public function status() {
        $this->render(['json' => StatusPresenter::to_array(['ok' => true, 'name' => '@app_name'])]);
    }

}

?>
