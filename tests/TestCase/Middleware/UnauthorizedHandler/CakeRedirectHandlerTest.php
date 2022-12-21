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
namespace Authorization\Test\TestCase\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Authorization\Middleware\UnauthorizedHandler\CakeRedirectHandler;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

class CakeRedirectHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Router::fullBaseUrl('http://localhost');
        $builder = Router::createRouteBuilder('/');
        $builder->connect(
            '/login',
            ['controller' => 'Users', 'action' => 'login'],
            ['_name' => 'login']
        );
        $builder->connect('/{controller}/{action}');
    }

    public function testHandleRedirectionDefault()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/admin/dashboard']
        );
        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?redirect=%2Fadmin%2Fdashboard', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionNamed()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/admin/dashboard']
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => [
                '_name' => 'login',
            ],
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?redirect=%2Fadmin%2Fdashboard', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionWithQuery()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/']
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => [
                '_name' => 'login',
                '?' => [
                    'foo' => 'bar',
                ],
            ],
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?foo=bar&redirect=%2F', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionNoQuery()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/']
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'queryParam' => null,
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectWithBasePath()
    {
        $handler = new CakeRedirectHandler();
        $exception = new Exception();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/admin/dashboard']
        );
        $request = $request->withAttribute('base', '/basedir');

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => [
                '_name' => 'login',
            ],
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(
            '/basedir/login?redirect=%2Fadmin%2Fdashboard',
            $response->getHeaderLine('Location')
        );
    }
}
