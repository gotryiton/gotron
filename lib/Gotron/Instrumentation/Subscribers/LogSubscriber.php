<?

namespace Gotron\Instrumentation;

use Gotron\Logging as Logging;

/**
 * Sends instrumentation to syslog
 *
 * @package Gotron;
 */
class LogSubscriber extends AbstractSubscriber{
    
    public function publish($tag, $start, $end, $params = array())
    {
        $time = round(($end - $start) * 1000, 4);
        $json_params = !empty($params) ? json_encode($params) : "";
        Logging::log("Took {$time}ms {$json_params}", $tag);
    }
}

?>