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

namespace Authorization\Test\TestCase\Middleware\Handler;

use Authorization\Exception\Exception;
use Authorization\Middleware\Handler\RedirectHandler;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

class RedirectHandlerTest extends TestCase
{
    public function testHandleRedirection()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();
        $response = new Response();

        $response = $handler->handle($exception, $request, $response, [
            'exceptions' => [
                Exception::class,
            ],
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?url=http%3A%2F%2Flocalhost%2F', $response->getHeaderLine('Location'));
    }
    public function testHandleRedirectionWithQuery()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();
        $response = new Response();

        $response = $handler->handle($exception, $request, $response, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => '/login?foo=bar'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?foo=bar&url=http%3A%2F%2Flocalhost%2F', $response->getHeaderLine('Location'));
    }

    public function testHandleRedirectionNoQuery()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();
        $response = new Response();

        $response = $handler->handle($exception, $request, $response, [
            'exceptions' => [
                Exception::class,
            ],
            'url' => '/users/login',
            'queryParam' => null,
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/users/login', $response->getHeaderLine('Location'));
    }

    public function testHandleException()
    {
        $handler = new RedirectHandler();

        $exception = new Exception();
        $request = new ServerRequest();
        $response = new Response();

        $this->expectException(Exception::class);
        $handler->handle($exception, $request, $response);
    }
}
