<?php

namespace TestApp;

use Gotron\Presenter;

class MultiVersionPresenter extends Presenter {

    public function as_array($array) {
        return ['name' => 'multi_4.0.1'];
    }

}

?>
