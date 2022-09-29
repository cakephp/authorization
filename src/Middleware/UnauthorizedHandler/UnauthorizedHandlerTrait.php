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
 * @since         2.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

trait UnauthorizedHandlerTrait
{
    /**
     * Handle exception.
     *
     * @param \Authorization\Exception\Exception $exception Exception to handle.
     * @param \Psr\Http\Message\ServerRequestInterface $request Request instance.
     * @param array|string $handler Handler config.
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleException(
        Exception $exception,
        ServerRequestInterface $request,
        $handler
    ): ResponseInterface {
        if (is_string($handler)) {
            $handler = [
                'className' => $handler,
            ];
        }
        if (!isset($handler['className'])) {
            throw new RuntimeException('Missing `className` key from handler config.');
        }

        $unauthorizedHandler = HandlerFactory::create($handler['className']);

        return $unauthorizedHandler->handle(
            $exception,
            $request,
            $handler
        );
    }
}
