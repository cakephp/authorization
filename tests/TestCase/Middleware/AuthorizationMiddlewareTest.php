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

use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityDecorator;
use Authorization\IdentityInterface;
use Authorization\Middleware\AuthorizationMiddleware;
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

class AuthorizationMiddlewareTest extends TestCase
{
    public function testInvokeService()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($service);

        $result = $middleware($request, $response, $next);

        $this->assertInstanceOf(RequestInterface::class, $result);
        $this->assertSame($service, $result->getAttribute('authorization'));
        $this->assertNull($result->getAttribute('identity'));
    }

    public function testInvokeApp()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);

        $application = $this->getMockBuilder(HttpApplicationInterface::class)
            ->setMethods(['authorization', 'middleware', 'routes', 'bootstrap', '__invoke'])
            ->getMock();
        $application
            ->expects($this->once())
            ->method('authorization')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(ResponseInterface::class)
            )
            ->willReturn($service);

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($application);

        $result = $middleware($request, $response, $next);

        $this->assertInstanceOf(RequestInterface::class, $result);
        $this->assertSame($service, $result->getAttribute('authorization'));
        $this->assertNull($result->getAttribute('identity'));
    }

    public function testInvokeAppMissing()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);

        $application = $this->getMockBuilder(HttpApplicationInterface::class)
            ->setMethods(['middleware', 'routes', 'bootstrap', '__invoke'])
            ->getMock();

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($application);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method `authorization` has not been defined in your `Application` class.');

        $result = $middleware($request, $response, $next);
    }

    public function testInvokeAppInvalid()
    {
        $application = $this->getMockBuilder(HttpApplicationInterface::class)
            ->setMethods(['middleware', 'routes', 'bootstrap', '__invoke'])
            ->getMock();

        $application = $this->getMockBuilder(HttpApplicationInterface::class)
            ->setMethods(['authorization', 'middleware', 'routes', 'bootstrap', '__invoke'])
            ->getMock();
        $application
            ->expects($this->once())
            ->method('authorization')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(ResponseInterface::class)
            )
            ->willReturn(new stdClass());

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($application);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid service returned from `authorization` method. ' .
            '`Authorization\AuthorizationServiceInterface` expected, `stdClass` given.'
        );

        $result = $middleware($request, $response, $next);
    }

    public function testInvokeInvalid()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Subject must be an instance of `Authorization\AuthorizationServiceInterface` ' .
            'or `Cake\Core\HttpApplicationInterface`, `stdClass` given.'
        );

        $middleware = new AuthorizationMiddleware(new stdClass());
    }

    public function testInvokeServiceWithIdentity()
    {
        $identity = [
            'id' => 1
        ];

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest)->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($service);

        $result = $middleware($request, $response, $next);

        $this->assertInstanceOf(RequestInterface::class, $result);
        $this->assertSame($service, $result->getAttribute('authorization'));
        $this->assertInstanceOf(IdentityInterface::class, $result->getAttribute('identity'));
        $this->assertEquals(1, $result->getAttribute('identity')['id']);
    }

    public function testIdentityInstance()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($service, [
            'id' => 1
        ]);

        $request = (new ServerRequest)->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($service);

        $result = $middleware($request, $response, $next);

        $this->assertInstanceOf(RequestInterface::class, $result);
        $this->assertSame($service, $result->getAttribute('authorization'));
        $this->assertInstanceOf(IdentityInterface::class, $result->getAttribute('identity'));
        $this->assertSame($identity, $result->getAttribute('identity'));
    }

    public function testCustomIdentity()
    {
        $identity = [
            'id' => 1
        ];

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest)->withAttribute('user', $identity);
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($service, [
            'identityDecorator' => function ($service, $identity) {
                return new IdentityDecorator($service, $identity);
            },
            'identityAttribute' => 'user'
        ]);

        $result = $middleware($request, $response, $next);

        $this->assertInstanceOf(RequestInterface::class, $result);
        $this->assertSame($service, $result->getAttribute('authorization'));
        $this->assertInstanceOf(IdentityInterface::class, $result->getAttribute('user'));
        $this->assertEquals(1, $result->getAttribute('user')['id']);
    }

    public function testInvalidIdentity()
    {
        $identity = [
            'id' => 1
        ];

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest)->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($service, [
            'identityDecorator' => 'stdClass'
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Object `stdClass` does not implement `Authorization\IdentityInterface`.');

        $result = $middleware($request, $response, $next);
    }
}
