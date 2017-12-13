<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization;

use ArrayAccess;
use InvalidArgumentException;

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
     * @var array|\ArrayAccess
     */
    protected $identity;

    /**
     * Authorization Service
     *
     * @var \Authorization\AuthorizationServiceInterface
     */
    protected $authorization;

    /**
     * Constructor
     *
     * @param \Authorization\AuthorizationServiceInterface $service The authorization service.
     * @param array|\ArrayAccess $identity Identity data
     * @param string $idField The field to use with getIdentifier()
     * @throws InvalidArgumentException When invalid identity data is passed.
     */
    public function __construct(AuthorizationServiceInterface $service, $identity)
    {
        if (!is_array($identity) && !$identity instanceof ArrayAccess) {
            $type = is_object($identity) ? get_class($identity) : gettype($identity);
            $message = sprintf('Identity data must be an `array` or implement `ArrayAccess` interface, `%s` given.', $type);
            throw new InvalidArgumentException($message);
        }

        $this->authorization = $service;
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function can($action, $resource)
    {
        return $this->authorization->can($this, $action, $resource);
    }

    /**
     * Delegate unknown methods to decorated identity.
     *
     * @param string $method The method being invoked.
     * @param arry $args The arguments for the method.
     * @return mixed
     */
    public function __call($method, $args)
    {
        $call = [$this->identity, $method];

        return $call(...$args);
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset Offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->identity[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->identity[$offset])) {
            return $this->identity[$offset];
        }

        return null;
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value Value
     * @return mixed
     */
    public function offsetSet($offset, $value)
    {
        return $this->identity[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset Offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->identity[$offset]);
    }
}
