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
namespace Authorization;

use Authorization\Policy\BeforePolicyInterface;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\ResolverInterface;
use RuntimeException;

class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Authorization policy resolver.
     *
     * @var \Authorization\Policy\ResolverInterface
     */
    protected $resolver;

    /**
     * Track whether or not authorization was checked.
     *
     * @var bool
     */
    protected $authorizationChecked = false;

    /**
     *
     * @param \Authorization\Policy\ResolverInterface $resolver Authorization policy resolver.
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function can(IdentityInterface $user, $action, $resource)
    {
        $this->authorizationChecked = true;
        $policy = $this->resolver->getPolicy($resource);

        if ($policy instanceof BeforePolicyInterface) {
            $result = $policy->before($user, $resource, $action);

            if (is_bool($result)) {
                return $result;
            }
            if ($result !== null) {
                throw new RuntimeException('Pre-authorization check must return `bool` or `null`.');
            }
        }

        $handler = $this->getCanHandler($policy, $action);
        $result = $handler($user, $resource);

        return $result === true;
    }

    /**
     * {@inheritDoc}
     */
    public function applyScope(IdentityInterface $user, $action, $resource)
    {
        $this->authorizationChecked = true;
        $policy = $this->resolver->getPolicy($resource);
        $handler = $this->getScopeHandler($policy, $action);

        return $handler($user, $resource);
    }

    /**
     * Returns a policy action handler.
     *
     * @param mixed $policy Policy object.
     * @param string $action Action name.
     * @return callable
     * @throws \Authorization\Policy\Exception\MissingMethodException
     */
    protected function getCanHandler($policy, $action)
    {
        $method = 'can' . ucfirst($action);

        if (!method_exists($policy, $method) && !method_exists($policy, ' __call')) {
            throw new MissingMethodException([$method, $action, get_class($policy)]);
        }

        return [$policy, $method];
    }

    /**
     * Returns a policy scope action handler.
     *
     * @param mixed $policy Policy object.
     * @param string $action Action name.
     * @return callable
     * @throws \Authorization\Policy\Exception\MissingMethodException
     */
    protected function getScopeHandler($policy, $action)
    {
        $method = 'scope' . ucfirst($action);

        if (!method_exists($policy, $method)) {
            throw new MissingMethodException([$method, $action, get_class($policy)]);
        }

        return [$policy, $method];
    }

    /**
     * {@inheritDoc}
     */
    public function authorizationChecked()
    {
        return $this->authorizationChecked;
    }

    /**
     * {@inheritDoc}
     */
    public function skipAuthorization()
    {
        $this->authorizationChecked = true;

        return $this;
    }
}
