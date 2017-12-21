<?php
namespace Cake\Authorization\Policy\Controller;

class LoggedInUserPolicy
{

    public function isLoggedIn($identity)
    {
        return !empty($identity);
    }

    public function __call($method, $args)
    {
        $this->isLoggedIn(...$args);
    }
}
