<?php
namespace Authorization;

interface AuthorizationAwareInterface
{

    /**
     * Set Bouncer
     *
     * @param \Authorization\BouncerInterface $bouncer
     * @return void
     */
    public function setBouncer(BouncerInterface $bouncer);

    /**
     * Get Bouncer
     *
     * @return \Authorization\BouncerInterface
     */
    public function getBouncer(): BouncerInterface;
}
