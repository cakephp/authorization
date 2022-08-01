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
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link https://cakephp.org CakePHP(tm) Project
 * @since 1.0.0
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Authorization\Test\TestCase\Middleware;

use Authorization\AuthorizationService;
use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Authorization\IdentityDecorator;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use RuntimeException;
use TestApp\Http\TestRequestHandler;
use TestApp\Policy\RequestPolicy;
use TestApp\Policy\RequestIdentityPolicy;
use Laminas\Diactoros\Uri;

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
        $handler = new TestRequestHandler();
        $middleware = new RequestAuthorizationMiddleware();
        $middleware->process($request, $handler);
    }

    public function testInvokeService()
    {
        $handler = new TestRequestHandler();

        $resolver = new MapResolver([
            ServerRequest::class => new RequestPolicy(),
        ]);

        $authService = new AuthorizationService($resolver);

        $identity = new IdentityDecorator($authService, [
            'id' => 1,
        ]);

        $request = (new ServerRequest([
            'uri' => new Uri('/articles/index'),
        ]))
            ->withParam('action', 'index')
            ->withParam('controller', 'Articles')
            ->withAttribute('identity', $identity);

        $request = $request->withAttribute('authorization', $authService);

        $middleware = new AuthorizationMiddleware($authService, [
            'requireAuthorizationCheck' => false,
        ]);
        $middleware->process($request, $handler);

        $middleware = new RequestAuthorizationMiddleware();
        $middleware->process($request, $handler);

        $request = $request
            ->withParam('action', 'add')
            ->withParam('controller', 'Articles');

        $this->expectException(ForbiddenException::class);
        $middleware = new RequestAuthorizationMiddleware();
        $middleware->process($request, $handler);
    }

    public function testInvokeServiceWithResult()
    {
        $handler = new TestRequestHandler();

        $resolver = new MapResolver([
            ServerRequest::class => new RequestPolicy(),
        ]);

        $authService = new AuthorizationService($resolver);

        $identity = new IdentityDecorator($authService, [
            'id' => 1,
        ]);

        $request = (new ServerRequest([
            'uri' => new Uri('/articles/index'),
        ]))
            ->withParam('action', 'index')
            ->withParam('controller', 'Articles')
            ->withAttribute('identity', $identity);

        $request = $request->withAttribute('authorization', $authService);

        $middleware = new AuthorizationMiddleware($authService, [
            'requireAuthorizationCheck' => false,
        ]);
        $middleware->process($request, $handler);

        $middleware = new RequestAuthorizationMiddleware([
            'method' => 'enter',
        ]);
        $middleware->process($request, $handler);

        $request = $request
            ->withParam('action', 'add')
            ->withParam('controller', 'Articles');

        $this->expectException(ForbiddenException::class);
        $middleware = new RequestAuthorizationMiddleware([
            'method' => 'enter',
        ]);

        try {
            $middleware->process($request, $handler);
        } catch (ForbiddenException $e) {
            $this->assertSame('wrong action', $e->getResult()->getReason());

            throw $e;
        }
    }

    public function testInvokeServiceWithoutEntity()
    {
        $request = (new ServerRequest([
            'uri' => new Uri('/articles/index'),
        ]))
            ->withParam('action', 'index')
            ->withParam('controller', 'Articles')
            ->withAttribute('identity', null);

        $handler = new TestRequestHandler();

        $resolver = new MapResolver([
            ServerRequest::class => new RequestPolicy(),
        ]);

        $authService = new AuthorizationService($resolver);
        $request = $request->withAttribute('authorization', $authService);

        $this->expectException(MissingIdentityException::class);
        $middleware = new RequestAuthorizationMiddleware();
        $middleware->process($request, $handler);
    }
}
