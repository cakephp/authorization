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
namespace Authorization\Test\TestCase\Middleware;

use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\Exception\AuthorizationRequiredException;
use Authorization\Exception\Exception;
use Authorization\IdentityDecorator;
use Authorization\IdentityInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Cake\Core\HttpApplicationInterface;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use stdClass;
use TestApp\Identity;
use TestApp\Policy\RequestPolicy;

/**
 * RequestAuthorizationMiddlewareTest
 */
class RequestAuthorizationMiddlewareTest extends TestCase
{
    public function testRuntimeExceptionWhenServiceIsMissing()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authorization\Middleware\RequestAuthorizationMiddleware could not find the authorization service in the request attribute. Make sure you added the AuthorizationMiddleware before this middleware or that you somehow else added the service to the requests `authorization` attribute.');

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request) {
            return $request;
        };
        $middleware = new RequestAuthorizationMiddleware();
        $middleware($request, $response, $next);
    }

    public function testInvokeService()
    {
        $request = (new ServerRequest())
            ->withParam('action', 'index')
            ->withParam('controller', 'Articles');

        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $resolver = new MapResolver([
            ServerRequest::class => RequestPolicy::class
        ]);

        $middleware = new AuthorizationMiddleware(new AuthorizationService($resolver));
        $middleware($request, $response, $next);

        $middleware = new RequestAuthorizationMiddleware();
        $middleware($request, $response, $next);
    }
}
