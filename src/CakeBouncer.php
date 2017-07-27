<?php
namespace Cake\Authorization;

use RuntimeException;

/**
 * Bouncer
 */
class CakeBouncer extends Bouncer
{

    public function __construct($identityResolver = null, array $policies = [])
    {
        parent::__construct($identityResolver, $policies);

        if (!class_exists('\Cake\Event\EventManager')) {
            throw new RuntimeException('Cake is not present');
        }

        $this->addCakeBeforeCheckEvent();
        $this->addCakeAfterCheckEvent();
    }

    protected function addCakeBeforeCheckEvent($event)
    {
        $callback = function($user, $ability) use ($event) {
            $event = new \Cake\Event\Event(
                'Authorization.' . $event,
                $this,
                compact('user', 'ability')
            );
            \Cake\Event\EventManager::instance()->dispatch($event);

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

    protected function addCakeAfterCheckEvent($event)
    {
        $callback = function($user, $ability) use ($event) {
            $event = new \Cake\Event\Event(
                'Authorization.' . $event,
                $this,
                compact('user', 'ability')
            );
            \Cake\Event\EventManager::instance()->dispatch($event);

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
