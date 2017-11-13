<?php
namespace Authorization;

use Cake\Event\Event;

class  TableAuthListener
{

    protected $bouncer;

    /**
     *
     */
    public function __construct(BouncerInterface $bouncer)
    {
        $this->bouncer = $bouncer;
    }

    public function initialize(Event $event)
    {
        $table = $event->getSubject();
        if ($table instanceof AuthorizationAwareInterface) {
            $table->setBouncer($this->bouncer);
        }
    }

    public function implementedEvents()
    {
        return ['Model.initialize' => 'initialize'];
    }
}
