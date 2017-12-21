<?php
namespace Authorization;

use Cake\Event\Event;

/**
 * Table Authorization Listener
 */
class TableAuthorizationListener
{
    /**
     * Bouncer
     *
     * @var \Authorization\BouncerInterface
     */
    protected $bouncer;

    /**
     * Constructor
     *
     * @param \Authorization\BouncerInterface
     */
    public function __construct(BouncerInterface $bouncer)
    {
        $this->bouncer = $bouncer;
    }

    /**
     * Initialize
     *
     * @param \Cake\Event\Event $event
     * @return void
     */
    public function initialize(Event $event) : void
    {
        $table = $event->getSubject();
        if ($table instanceof AuthorizationAwareInterface) {
            $table->setBouncer($this->bouncer);
        }
    }

    /**
     * Implemented events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.initialize' => 'initialize'
        ];
    }
}
