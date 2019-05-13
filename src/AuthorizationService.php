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
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\ResolverInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;

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
     * @inheritDoc
     */
    public function can(?IdentityInterface $user, string $action, $resource): ResultInterface
    {
        $this->authorizationChecked = true;
        $policy = $this->resolver->getPolicy($resource);

        if ($policy instanceof BeforePolicyInterface) {
            $result = $policy->before($user, $resource, $action);
            $result = $this->createResultInstance($result);

            if ($result !== null) {
                return $result;
            }
        }

        $handler = $this->getCanHandler($policy, $action);
        $result = $handler($user, $resource);

        return $this->createResultInstance($result);
    }

    /**
     * Converts boolean result from policy class to Result instance.
     *
     * @param mixed $result Result from policy class instance.
     * @return \Authorization\Policy\ResultInterface|null
     * @throws \Authorization\Exception\Exception If $result argument is not a boolean or ResultInterface instance.
     */
    protected function createResultInstance($result): ?ResultInterface
    {
        if (is_bool($result)) {
            return new Result($result);
        }

        if ($result === null || $result instanceof ResultInterface) {
            return $result;
        }

        $message = sprintf(
            'Pre-authorization check must return `%s`, `bool` or `null`.',
            ResultInterface::class
        );
        throw new Exception($message);
    }

    /**
     * @inheritDoc
     */
    public function applyScope(?IdentityInterface $user, string $action, $resource)
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
    protected function getCanHandler($policy, $action): callable
    {
        $method = 'can' . ucfirst($action);

        if (!method_exists($policy, $method) && !method_exists($policy, '__call')) {
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
    protected function getScopeHandler($policy, $action): callable
    {
        $method = 'scope' . ucfirst($action);

        if (!method_exists($policy, $method)) {
            throw new MissingMethodException([$method, $action, get_class($policy)]);
        }

        return [$policy, $method];
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
