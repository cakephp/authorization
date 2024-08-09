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
use Authorization\Middleware\UnauthorizedHandler\RedirectHandler;
use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RedirectHandlerTest extends TestCase
{
    public function testHandleRedirection()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_METHOD' => 'GET']
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?redirect=%2F', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionWithQuery()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/path',
                'QUERY_STRING' => 'key=value',
            ]
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => '/login?foo=bar',
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?foo=bar&redirect=%2Fpath%3Fkey%3Dvalue', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionNoQuery()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_METHOD' => 'GET']
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => '/users/login',
            'queryParam' => null,
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users/login', $response->getHeaderLine('Location'));
    }

    public static function httpMethodProvider()
    {
        return [
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['PATCH'],
            ['OPTIONS'],
            ['HEAD'],
        ];
    }

    #[DataProvider('httpMethodProvider')]
    public function testHandleRedirectionIgnoreNonIdempotentMethods($method)
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => $method,
                'PATH_INFO' => '/path',
                'QUERY_STRING' => 'key=value',
            ]
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => '/login?foo=bar',
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?foo=bar', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectWithBasePath()
    {
        $handler = new RedirectHandler();
        $exception = new Exception();

        Configure::write('App.base', '/basedir');
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/path', 'REQUEST_METHOD' => 'GET']
        );

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => '/basedir/login',
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(
            '/basedir/login?redirect=%2Fpath',
            $response->getHeaderLine('Location')
        );
    }

    public function testHandleException()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);

        $this->expectException(Exception::class);
        $handler->handle($exception, $request);
    }
}
