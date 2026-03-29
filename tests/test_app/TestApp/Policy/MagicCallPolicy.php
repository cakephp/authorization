<?php
declare(strict_types=1);

namespace TestApp\Policy;

/**
 * For testing a policy that implements __call()
 */
class MagicCallPolicy
{
    /**
     * Magic call
     *
     * @param string $name Name
     * @param array $arguments Arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'canDoThat') {
            return true;
        }

        return $name === 'scopeThis';
    }
}
