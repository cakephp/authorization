<?php
namespace TestApp;

use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityDecorator;

class Identity extends IdentityDecorator
{
    public function setService(AuthorizationServiceInterface $service)
    {
    }
}
