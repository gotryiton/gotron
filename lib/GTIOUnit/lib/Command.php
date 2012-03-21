<?php
/**
 * Overrides the TextUI_Command to call the correct TestRunner
 */
class GtioTextUICommand extends PHPUnit_TextUI_Command {
    /**
     * @param boolean $exit
     */
    public static function main($exit = true) {
        $command = new GtioTextUICommand;
        return $command->run($_SERVER['argv'], $exit);
    }
    
    /**
     * Create a TestRunner, override in subclasses.
     *
     * @return PHPUnit_TextUI_TestRunner
     * @since  Method available since Release 3.6.0
     */
    protected function createRunner() {
        return new GtioTestRunner($this->arguments['loader']);
    }
}
