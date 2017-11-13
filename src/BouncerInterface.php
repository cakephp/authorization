<?php
namespace Authorization;

interface BouncerInterface
{
    public function allows($ability, array $arguments = []);

    public function denies($ability, array $arguments = []);

    public function setIdentityResolver(callable $resolver);
}
