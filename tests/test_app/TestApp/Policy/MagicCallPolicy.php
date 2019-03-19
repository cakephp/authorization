<?php
declare(strict_types=1);
namespace TestApp\Policy;

use Phauthentic\Authorization\Policy\Result;

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
    public function __call($name, $arguments)
    {
        if ($name === 'canDoThat') {
            return new Result(true);
        }

        return new Result(false);
    }
}
