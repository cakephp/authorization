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

use Authorization\Middleware\UnauthorizedHandler\HandlerFactory;
use Cake\TestSuite\TestCase;
use RuntimeException;
use TestApp\Middleware\UnauthorizedHandler\SuppressHandler;

class HandlerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $handler = HandlerFactory::create('Suppress');
        $this->assertInstanceOf(SuppressHandler::class, $handler);
    }

    public function testCreateMissing()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Handler `Foo` does not exist.');

        HandlerFactory::create('Foo');
    }

    public function testCreateInvalid()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Handler should implement `Authorization\Middleware\UnauthorizedHandler\HandlerInterface`, got `stdClass`.');
        HandlerFactory::create('\stdClass');
    }
}
