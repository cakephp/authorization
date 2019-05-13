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
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * Description of CakeRedirectHandlerTest
 */
class CakeRedirectHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Router::reload();
        Router::fullBaseUrl('http://localhost');
        Router::connect(
            '/login',
            ['controller' => 'Users', 'action' => 'login'],
            ['_name' => 'login']
        );
        Router::connect('/:controller/:action');
    }

    public function testHandleRedirectionDefault()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?redirect=http%3A%2F%2Flocalhost%2F', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionNamed()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => [
                '_name' => 'login',
            ],
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?redirect=http%3A%2F%2Flocalhost%2F', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionWithQuery()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();

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

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?foo=bar&redirect=http%3A%2F%2Flocalhost%2F', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionNoQuery()
    {
        $handler = new CakeRedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();

        $response = $handler->handle($exception, $request, [
            'exceptions' => [
                Exception::class,
            ],
            'queryParam' => null,
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }
}
