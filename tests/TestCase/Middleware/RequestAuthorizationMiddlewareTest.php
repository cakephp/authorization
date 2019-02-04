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
namespace Authorization\Test\TestCase\Middleware;

use Authorization\AuthorizationService;
use Authorization\Exception\ForbiddenException;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use RuntimeException;
use TestApp\Policy\RequestPolicy;
use Zend\Diactoros\Uri;

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
        $request = (new ServerRequest([
                'uri' => new Uri('/articles/index')
            ]))
            ->withParam('action', 'index')
            ->withParam('controller', 'Articles');

        $response = new Response();
        $next = function ($request, $response) {
            return $response;
        };

        $resolver = new MapResolver([
            ServerRequest::class => new RequestPolicy()
        ]);

        $authService = new AuthorizationService($resolver);
        $request = $request->withAttribute('authorization', $authService);

        $middleware = new AuthorizationMiddleware($authService, [
            'requireAuthorizationCheck' => false
        ]);
        $middleware($request, $response, $next);

        $middleware = new RequestAuthorizationMiddleware();
        $middleware($request, $response, $next);

        $request = $request
            ->withParam('action', 'add')
            ->withParam('controller', 'Articles');

        $this->expectException(ForbiddenException::class);
        $middleware = new RequestAuthorizationMiddleware();
        $middleware($request, $response, $next);
    }

    public function testInvokeServiceWithResult()
    {
        $request = (new ServerRequest([
                'uri' => new Uri('/articles/index')
            ]))
            ->withParam('action', 'index')
            ->withParam('controller', 'Articles');

        $response = new Response();
        $next = function ($request, $response) {
            return $response;
        };

        $resolver = new MapResolver([
            ServerRequest::class => new RequestPolicy()
        ]);

        $authService = new AuthorizationService($resolver);
        $request = $request->withAttribute('authorization', $authService);

        $middleware = new AuthorizationMiddleware($authService, [
            'requireAuthorizationCheck' => false
        ]);
        $middleware($request, $response, $next);

        $middleware = new RequestAuthorizationMiddleware([
            'method' => 'enter',
        ]);
        $middleware($request, $response, $next);

        $request = $request
            ->withParam('action', 'add')
            ->withParam('controller', 'Articles');

        $this->expectException(ForbiddenException::class);
        $middleware = new RequestAuthorizationMiddleware([
            'method' => 'enter',
        ]);

        try {
            $middleware($request, $response, $next);
        } catch (ForbiddenException $e) {
            $this->assertEquals('wrong action', $e->getResult()->getReason());

            throw $e;
        }
    }
}
