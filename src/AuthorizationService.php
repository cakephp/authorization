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

class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Authorization policy resolver.
     *
     * @var \Authorization\Policy\ResolverInterface
     */
    protected $resolver;

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
        $policy = $this->resolver->getPolicy($resource);

        if ($policy instanceof BeforePolicyInterface) {
            if (!$policy->before($user, $resource)) {
                return false;
            }

            try {
                $handler = $this->getHandler($policy, $action);
            } catch (MissingMethodException $e) {
                return true;
            }
        } else {
            $handler = $this->getHandler($policy, $action);
        }

        $result = $handler($user, $resource);

        return $result === true;
    }

    /**
     * Returns a policy action handler.
     *
     * @param mixed $policy Policy object.
     * @param string $action Action name.
     * @return callable
     * @throws \Authorization\Policy\Exception\MissingMethodException
     */
    protected function getHandler($policy, $action)
    {
        $method = 'can' . ucfirst($action);

        if (!method_exists($policy, $method)) {
            throw new MissingMethodException([$method, $action, get_class($policy)]);
        }

        return [$policy, $method];
    }
}
