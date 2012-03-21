<?php

namespace Gotron\Jobs;

/**
 * Test BeanstalkerJob
 *
 */

class UnitTestJob {
    public function perform() {
        echo "This is the output from " . $this->data['name'] . "\n";
    }
}

?>