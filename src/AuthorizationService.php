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

use Authorization\Exception\Exception;
use Authorization\Policy\BeforePolicyInterface;
use Authorization\Policy\BeforeScopeInterface;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\ResolverInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;
use Closure;

class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Authorization policy resolver.
     *
     * @var \Authorization\Policy\ResolverInterface
     */
    protected ResolverInterface $resolver;

    /**
     * Track whether or not authorization was checked.
     *
     * @var bool
     */
    protected bool $authorizationChecked = false;

    /**
     * @param \Authorization\Policy\ResolverInterface $resolver Authorization policy resolver.
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public function can(?IdentityInterface $user, string $action, $resource, ...$optionalArgs): bool
    {
        $result = $this->performCheck($user, $action, $resource, ...$optionalArgs);

        return is_bool($result) ? $result : $result->getStatus();
    }

    /**
     * @inheritDoc
     */
    public function canResult(?IdentityInterface $user, string $action, $resource, ...$optionalArgs): ResultInterface
    {
        $result = $this->performCheck($user, $action, $resource, ...$optionalArgs);

        return is_bool($result) ? new Result($result) : $result;
    }

    /**
     * Check whether the provided user can perform an action on a resource.
     *
     * @param \Authorization\IdentityInterface|null $user The user to check permissions for.
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @param mixed $optionalArgs Multiple additional arguments which are passed on
     * @return \Authorization\Policy\ResultInterface|bool
     */
    protected function performCheck(
        ?IdentityInterface $user,
        string $action,
        mixed $resource,
        mixed ...$optionalArgs
    ): bool|ResultInterface {
        $this->authorizationChecked = true;
        $policy = $this->resolver->getPolicy($resource);

        if ($policy instanceof BeforePolicyInterface) {
            $result = $policy->before($user, $resource, $action);

            if ($result !== null) {
                return $result;
            }
        }

        $handler = $this->getCanHandler($policy, $action);
        $result = $handler($user, $resource, ...$optionalArgs);

        assert(
            is_bool($result) || $result instanceof ResultInterface,
            new Exception(sprintf(
                'Authorization check method must return `%s` or `bool`.',
                ResultInterface::class
            ))
        );

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function applyScope(?IdentityInterface $user, string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        $this->authorizationChecked = true;
        $policy = $this->resolver->getPolicy($resource);

        if ($policy instanceof BeforeScopeInterface) {
            $result = $policy->beforeScope($user, $resource, $action);

            if ($result !== null) {
                return $result;
            }
        }

        $handler = $this->getScopeHandler($policy, $action);

        return $handler($user, $resource, ...$optionalArgs);
    }

    /**
     * Returns a policy action handler.
     *
     * @param mixed $policy Policy object.
     * @param string $action Action name.
     * @return \Closure
     * @throws \Authorization\Policy\Exception\MissingMethodException
     */
    protected function getCanHandler(mixed $policy, string $action): Closure
    {
        $method = 'can' . ucfirst($action);

        assert(
            method_exists($policy, $method) || method_exists($policy, '__call'),
            new MissingMethodException([$method, $action, get_class($policy)])
        );

        return [$policy, $method](...);
    }

    /**
     * Returns a policy scope action handler.
     *
     * @param mixed $policy Policy object.
     * @param string $action Action name.
     * @return \Closure
     * @throws \Authorization\Policy\Exception\MissingMethodException
     */
    protected function getScopeHandler(mixed $policy, string $action): Closure
    {
        $method = 'scope' . ucfirst($action);

        assert(
            method_exists($policy, $method) || method_exists($policy, '__call'),
            new MissingMethodException([$method, $action, get_class($policy)])
        );

        return [$policy, $method](...);
    }

    /**
     * @inheritDoc
     */
    public function authorizationChecked(): bool
    {
        return $this->authorizationChecked;
    }

    /**
     * @inheritDoc
     */
    public function skipAuthorization()
    {
        $this->authorizationChecked = true;

        return $this;
    }
}
