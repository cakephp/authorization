<?php
declare(strict_types=1);

namespace TestApp;

use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityDecorator;

class Identity extends IdentityDecorator
{
    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    public function setService(AuthorizationServiceInterface $service): void
    {
        $this->authorization = $service;
    }

    public function getService(): AuthorizationServiceInterface
    {
        return $this->authorization;
    }
}
