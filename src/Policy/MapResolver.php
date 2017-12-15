<?php
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
namespace Authorization\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use InvalidArgumentException;

class MapResolver implements ResolverInterface
{
    /**
     * Resource to policy class name map.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Constructor.
     *
     * Takes a resource to policy class name map, for example:
     * ```
     * [
     *     \App\Service\Resource::class => \App\Policy\ResourcePolicy::class
     * ]
     * ```
     *
     * @param array $map Resource to policy class name map.
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $resourceClass => $policyClass) {
            $this->map($resourceClass, $policyClass);
        }
    }

    /**
     * Maps a resource class to the policy class name.
     *
     * @param string $resourceClass A resource class name.
     * @param string $policyClass A policy class name.
     * @return $this
     * @throws \InvalidArgumentException When a policy class does not exist.
     */
    public function map($resourceClass, $policyClass)
    {
        if (!class_exists($policyClass)) {
            $message = sprintf('Policy class `%s` does not exist.', $policyClass);
            throw new InvalidArgumentException($message);
        }

        $this->map[$resourceClass] = $policyClass;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException When a resource is not an object.
     * @throws \Authorization\Policy\Exception\MissingPolicyException When a policy for a resource has not been defined.
     */
    public function getPolicy($resource)
    {
        if (!is_object($resource)) {
            $message = sprintf('Resource must be an object, `%s` given.', gettype($resource));
            throw new InvalidArgumentException($message);
        }

        $class = get_class($resource);

        if (!isset($this->map[$class])) {
            throw new MissingPolicyException([$class]);
        }

        return new $this->map[$class];
    }
}
