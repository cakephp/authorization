<?php
namespace Cake\Authorization;

use Cake\Event\EventDispatcherTrait;
use RuntimeException;

/**
 * Bouncer
 */
class CakeBouncer extends Bouncer
{

    use EventDispatcherTrait;

    public function __construct($identityResolver = null, array $policies = [])
    {
        parent::__construct($identityResolver, $policies);

        if (!class_exists('\Cake\Event\EventManager')) {
            throw new RuntimeException('\Cake\Event namespace is not present');
        }

        $this->addCakeBeforeCheckEvent();
        $this->addCakeAfterCheckEvent();
    }

    protected function addCakeBeforeCheckEvent()
    {
        $callback = function($user, $ability) {
            $event = $this->dispatchEvent('Authorization.beforeResolve', compact('user', 'ability'));

            if ($event->isStopped()) {
                return false;
            }

            $result = $event->getResult();
            if (!is_bool($result)) {
                return false;
            }

            return $result;
        };

        $this->addBeforeCallback($callback);
    }

    protected function addCakeAfterCheckEvent()
    {
        $callback = function($user, $ability) {
            $event = $this->dispatchEvent('Authorization.afterResolve', compact('user', 'ability'));

            if ($event->isStopped()) {
                return false;
            }

            $result = $event->getResult();
            if (!is_bool($result)) {
                return false;
            }

            return $result;
        };

        $this->addAfterCallback($callback);
    }
}
