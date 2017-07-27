<?php
namespace Cake\Authorization;

use Authorization\BouncerInterface;
use Authorization\PolicyLocator;

trait BouncerTrait {

    protected $bouncerClass = Bouncer::class;
    protected $bouncer;

    public function getBouncer()
    {
        if (!empty($this->bouncer)) {
            return $this->bouncer;
        }

        $this->bouncer = new $this->bouncerClass();

        $locator = new PolicyLocator();
        $policyClass = $locator->locate($this);
        if (!empty($policyClass)) {
            $this->bouncer->addPolicyFor($this, $policyClass);
        }

        return $this->bouncer;
    }

    public function setBouncer(BouncerInterface $bouncer)
    {
        $this->bouncer = $bouncer;
    }

    public function can($ability, ...$args)
    {
        return $this->getBouncer()->allowed($ability, ...$args);
    }
}
