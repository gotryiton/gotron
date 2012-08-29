<?php

namespace TestApp;

use Gotron\Presenter;

class OtherVersionPresenter extends Presenter {

    public function as_array($array) {
        return ['name' => '4'];
    }

}

?>
