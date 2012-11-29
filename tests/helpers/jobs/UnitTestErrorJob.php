<?php

namespace TestApp;

use Gotron\Exception;

class UnitTestErrorJob {

    public function perform() {
        throw new Exception("Testing an exception");
    }

}

?>

