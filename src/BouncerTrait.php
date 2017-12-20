<?php
namespace Authorization;

use Authorization\BouncerInterface;
use Authorization\PolicyLocator;

/**
 * Bouncer Trait
 */
trait BouncerTrait {

    /**
     * Default Bouncer Class
     *
     * @var string
     */
    protected $bouncerClass = Bouncer::class;

    /**
     * Bouncer Instance
     *
     * @var null|\Authorization\BouncerInterface
     */
    protected $bouncer;

    /**
     *
     */
    protected function getPolicy() {
        $locator = new PolicyLocator();

        return $locator->locate($this);
    }

    /**
     * Get Bouncer
     *
     * @return \Authorization\BouncerInterface
     */
    public function getBouncer() : BouncerInterface
    {
        if (!empty($this->bouncer)) {
            return $this->bouncer;
        }

        $this->bouncer = new $this->bouncerClass();
        $policyClass = $this->getPolicy();

        if (!empty($policyClass)) {
            $this->bouncer->addPolicyFor($this, $policyClass);
        }

        return $this->bouncer;
    }

    /**
     * Set Bouncer Instance
     *
     * @param \Authorization\BouncerInterface
     * @return void
     */
    public function setBouncer(BouncerInterface $bouncer) : void
    {
        $this->bouncer = $bouncer;
    }

    /**
     *
     */
    public function can($ability, ...$args)
    {
        return $this->getBouncer()->allowed($ability, ...$args);
    }
}
