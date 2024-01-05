<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization;

use ArrayAccess;
use Authorization\Policy\ResultInterface;
use BadMethodCallException;

/**
 * An decorator implementing the IdentityInterface.
 *
 * This decorator is intended to wrap the application defined identity
 * object and proxy attributes/methods to and 'mixin' the can() method.
 *
 * The decorated identity must implement ArrayAccess or be an array.
 */
class IdentityDecorator implements IdentityInterface
{
    /**
     * Identity data
     *
     * @var \ArrayAccess|array
     */
    protected ArrayAccess|array $identity;

    /**
     * Authorization Service
     *
     * @var \Authorization\AuthorizationServiceInterface
     */
    protected AuthorizationServiceInterface $authorization;

    /**
     * Constructor
     *
     * @param \Authorization\AuthorizationServiceInterface $service The authorization service.
     * @param \ArrayAccess|array $identity Identity data
     * @throws \InvalidArgumentException When invalid identity data is passed.
     */
    public function __construct(AuthorizationServiceInterface $service, ArrayAccess|array $identity)
    {
        $this->authorization = $service;
        $this->identity = $identity;
    }

    /**
     * @inheritDoc
     */
    public function can(string $action, mixed $resource): bool
    {
        return $this->authorization->can($this, $action, $resource);
    }

    /**
     * @inheritDoc
     */
    public function canResult(string $action, mixed $resource): ResultInterface
    {
        return $this->authorization->canResult($this, $action, $resource);
    }

    /**
     * @inheritDoc
     */
    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        return $this->authorization->applyScope($this, $action, $resource, ...$optionalArgs);
    }

    /**
     * @inheritDoc
     */
    public function getOriginalData(): ArrayAccess|array
    {
        if (
            $this->identity
            && !is_array($this->identity)
            && method_exists($this->identity, 'getOriginalData')
        ) {
            return $this->identity->getOriginalData();
        }

        return $this->identity;
    }

    /**
     * Delegate unknown methods to decorated identity.
     *
     * @param string $method The method being invoked.
     * @param array $args The arguments for the method.
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        if (!is_object($this->identity)) {
            throw new BadMethodCallException("Cannot call `{$method}`. Identity data is not an object.");
        }
        $call = [$this->identity, $method];

        return $call(...$args);
    }

    /**
     * Delegate property access to decorated identity.
     *
     * @param string $property The property to read.
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        return $this->identity->{$property};
    }

    /**
     * Delegate property isset to decorated identity.
     *
     * @param string $property The property to read.
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return isset($this->identity->{$property});
    }

    /**
     * Whether a offset exists
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset Offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->identity[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset Offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (isset($this->identity[$offset])) {
            return $this->identity[$offset];
        }

        return null;
    }

    /**
     * Offset to set
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value Value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->identity[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset Offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->identity[$offset]);
    }
}
