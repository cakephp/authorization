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

use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Exception\AuthorizationRequiredException;
use Authorization\Exception\Exception;
use Authorization\IdentityDecorator;
use Authorization\IdentityInterface;
use Authorization\Middleware\AuthorizationMiddleware;
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
use TypeError;

class AuthorizationMiddlewareTest extends TestCase
{
    public function testInvokeService()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request, $response) use ($service) {
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame($service, $request->getAttribute('authorization'));
            $this->assertNull($request->getAttribute('identity'));

            return $response;
        };

        $middleware = new AuthorizationMiddleware($service, ['requireAuthorizationCheck' => false]);
        $middleware($request, $response, $next);
    }

    public function testInvokeAuthorizationRequiredError()
    {
        $this->expectException(AuthorizationRequiredException::class);

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $service->expects($this->once())
            ->method('authorizationChecked')
            ->will($this->returnValue(false));

        $request = (new ServerRequest())->withAttribute('identity', ['id' => 1]);
        $response = new Response();
        $next = function ($request, $response) {
            // Don't call any auth methods.
            return $response;
        };

        $middleware = new AuthorizationMiddleware($service, ['requireAuthorizationCheck' => true]);
        $result = $middleware($request, $response, $next);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame($service, $request->getAttribute('authorization'));
    }

    public function testInvokeApp()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $provider = $this->createMock(AuthorizationServiceProviderInterface::class);
        $provider
            ->expects($this->once())
            ->method('getAuthorizationService')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(ResponseInterface::class)
            )
            ->willReturn($service);

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request, $response) use ($service) {
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame($service, $request->getAttribute('authorization'));
            $this->assertNull($request->getAttribute('identity'));

            return $response;
        };

        $middleware = new AuthorizationMiddleware($provider, ['requireAuthorizationCheck' => false]);
        $middleware($request, $response, $next);
    }

    public function testInvokeAppInvalid()
    {
        $provider = $this->createMock(AuthorizationServiceProviderInterface::class);
        $provider
            ->expects($this->once())
            ->method('getAuthorizationService')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                $this->isInstanceOf(ResponseInterface::class)
            )
            ->willReturn(new stdClass());

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request, $response) {
            return $response;
        };

        $middleware = new AuthorizationMiddleware($provider, ['requireAuthorizationCheck' => false]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('instance of stdClass returned');

        $middleware($request, $response, $next);
    }

    public function testInvokeInvalid()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request, $response) {
            return $response;
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Subject must be an instance of `Authorization\AuthorizationServiceInterface` ' .
            'or `Authorization\AuthorizationServiceProviderInterface`, `stdClass` given.'
        );

        $middleware = new AuthorizationMiddleware(new stdClass());
    }

    public function testInvokeServiceWithIdentity()
    {
        $identity = [
            'id' => 1,
        ];

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest())->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request, $response) use ($service) {
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame($service, $request->getAttribute('authorization'));
            $this->assertInstanceOf(IdentityInterface::class, $request->getAttribute('identity'));
            $this->assertEquals(1, $request->getAttribute('identity')['id']);

            return $response;
        };

        $middleware = new AuthorizationMiddleware($service, ['requireAuthorizationCheck' => false]);
        $middleware($request, $response, $next);
    }

    public function testIdentityInstance()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($service, [
            'id' => 1,
        ]);

        $request = (new ServerRequest())->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request, $response) use ($service, $identity) {
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame($service, $request->getAttribute('authorization'));
            $this->assertInstanceOf(IdentityInterface::class, $request->getAttribute('identity'));
            $this->assertSame($identity, $request->getAttribute('identity'));

            return $response;
        };

        $middleware = new AuthorizationMiddleware($service, ['requireAuthorizationCheck' => false]);
        $middleware($request, $response, $next);
    }

    public function testCustomIdentity()
    {
        $identity = [
            'id' => 1,
        ];

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest())->withAttribute('user', $identity);
        $response = new Response();
        $next = function ($request, $response) use ($service) {
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame($service, $request->getAttribute('authorization'));
            $this->assertInstanceOf(IdentityInterface::class, $request->getAttribute('user'));
            $this->assertEquals(1, $request->getAttribute('user')['id']);

            return $response;
        };

        $middleware = new AuthorizationMiddleware($service, [
            'identityDecorator' => function ($service, $identity) {
                return new IdentityDecorator($service, $identity);
            },
            'identityAttribute' => 'user',
            'requireAuthorizationCheck' => false,
        ]);

        $middleware($request, $response, $next);
    }

    public function testCustomIdentityDecorator()
    {
        $identity = new Identity([
            'id' => 1,
        ]);

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest())->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request, $response) use ($service, $identity) {
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame($service, $request->getAttribute('authorization'));
            $this->assertInstanceOf(IdentityInterface::class, $request->getAttribute('identity'));
            $this->assertSame($identity, $request->getAttribute('identity'));
            $this->assertSame($service, $request->getAttribute('identity')->getService());

            return $response;
        };

        $middleware = new AuthorizationMiddleware($service, [
            'identityDecorator' => function ($service, $identity) {
                $identity->setService($service);

                return $identity;
            },
            'requireAuthorizationCheck' => false,
        ]);
        $middleware($request, $response, $next);
    }

    public function testInvalidIdentity()
    {
        $identity = [
            'id' => 1,
        ];

        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = (new ServerRequest())->withAttribute('identity', $identity);
        $response = new Response();
        $next = function ($request) {
            return $request;
        };

        $middleware = new AuthorizationMiddleware($service, [
            'identityDecorator' => stdClass::class,
            'requireAuthorizationCheck' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid identity returned by decorator. `stdClass` does not implement `Authorization\IdentityInterface`.');

        $middleware($request, $response, $next);
    }

    public function testUnauthorizedHandler()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = new ServerRequest();
        $response = new Response();
        $next = function () {
            throw new Exception();
        };

        $middleware = new AuthorizationMiddleware($service, ['requireAuthorizationCheck' => false]);

        $this->expectException(Exception::class);
        $middleware($request, $response, $next);
    }

    public function testUnauthorizedHandlerSuppress()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = new ServerRequest();
        $response = new Response();
        $next = function () {
            throw new Exception();
        };

        $middleware = new AuthorizationMiddleware($service, [
            'requireAuthorizationCheck' => false,
            'unauthorizedHandler' => 'Suppress',
        ]);

        $result = $middleware($request, $response, $next);
        $this->assertSame($response, $result);
    }

    public function testUnauthorizedHandlerRequireAuthz()
    {
        $service = $this->createMock(AuthorizationServiceInterface::class);
        $request = new ServerRequest();
        $response = new Response();
        $next = function () {
            throw new Exception();
        };

        $middleware = new AuthorizationMiddleware($service, [
            'requireAuthorizationCheck' => true,
            'unauthorizedHandler' => 'Suppress',
        ]);

        $result = $middleware($request, $response, $next);
        $this->assertSame($response, $result);
    }
}
