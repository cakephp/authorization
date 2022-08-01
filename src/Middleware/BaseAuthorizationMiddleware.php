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

namespace Authorization\Middleware;

use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Exception\AuthorizationRequiredException;
use Authorization\Exception\Exception;
use Authorization\IdentityInterface;
use Authorization\Middleware\UnauthorizedHandler\HandlerFactory;
use Authorization\Middleware\UnauthorizedHandler\HandlerInterface;
use Cake\Core\InstanceConfigTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Authorization Middleware.
 *
 * Injects the authorization service and decorated identity objects into the request object as attributes.
 */
abstract class BaseAuthorizationMiddleware implements MiddlewareInterface
{
    use InstanceConfigTrait;

    /**
     * Default config.
     *
     * - `identityDecorator` Identity decorator class name or a callable.
     *   Defaults to IdentityDecorator
     * - `identityAttribute` Attribute name the identity is stored under.
     *   Defaults to 'identity'
     * - `requireAuthorizationCheck` When true the middleware will raise an exception
     *   if no authorization checks were done. This aids in ensuring that all actions
     *   check authorization. It is intended as a development aid and not to be relied upon
     *   in production. Defaults to `true`.
     * - `unauthorizedHandler`
     * 
     * - `authorizationAttribute`
     * 
     * - `method`
     *
     * @var array
     */
    protected $_defaultConfig = [
        'identityDecorator' => null,
        'identityAttribute' => 'identity',
        'requireAuthorizationCheck' => true,
        'unauthorizedHandler' => 'Authorization.Exception',
        'authorizationAttribute' => 'authorization',
        'method' => 'access',
    ];

    /**
     * Callable implementation for the middleware stack.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    abstract public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    /**
     * Returns unauthorized handler.
     *
     * @return \Authorization\Middleware\UnauthorizedHandler\HandlerInterface
     */
    protected function getHandler(): HandlerInterface
    {
        $handler = $this->getConfig('unauthorizedHandler');
        if (!is_array($handler)) {
            $handler = [
                'className' => $handler,
            ];
        }
        if (!isset($handler['className'])) {
            throw new RuntimeException('Missing `className` key from handler config.');
        }

        return HandlerFactory::create($handler['className']);
    }
}
