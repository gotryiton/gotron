<?

namespace Gotron\Instrumentation;

use Gotron\Instrumentation\StatsD;

/**
 * Sends instrumentation to Statsd
 *
 * @package Gotron;
 */
class StatsdSubscriber extends AbstractSubscriber{
    
    public function publish($tag, $start, $end, $params = array()) {
        StatsD::timing($tag, ($end - $start) * 1000);
    }
}

?>