<?php

/**
 * Overrides the default TextUI_TestRunner to allow for a before and after run method
 *
 */
class GtioTestRunner extends PHPUnit_TextUI_TestRunner {

    public function doRun(PHPUnit_Framework_Test $suite, array $arguments = array()) {
        if (isset($GLOBALS['PHPUNIT_BEFORE_RUN']) && is_callable($GLOBALS['PHPUNIT_BEFORE_RUN'])){
            $GLOBALS['PHPUNIT_BEFORE_RUN']();
        }

        $result = parent::doRun($suite, $arguments);

        if (isset($GLOBALS['PHPUNIT_AFTER_RUN']) && is_callable($GLOBALS['PHPUNIT_AFTER_RUN'])){
            $GLOBALS['PHPUNIT_AFTER_RUN']();
        }

        return $result;
    }

}
