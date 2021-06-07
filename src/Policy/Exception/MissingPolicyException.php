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
namespace Authorization\Policy\Exception;

use Authorization\Exception\Exception;
use Cake\Datasource\RepositoryInterface;
use Throwable;

class MissingPolicyException extends Exception
{
    /**
     * Template string that has attributes sprintf()'ed into it.
     *
     * @var string
     */
    protected $_messageTemplate = 'Policy for `%s` has not been defined.';

    /**
     * @param object|string|array $resource Either the resource instance, a string of the error message, or an array of attributes
     *   that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param int|null $code The code of the error, is also the HTTP status code for the error.
     * @param \Throwable|null $previous the previous exception.
     */
    public function __construct($resource, ?int $code = null, ?Throwable $previous = null)
    {
        if (is_object($resource)) {
            $resourceClass = get_class($resource);
            if (
                method_exists($resource, 'getRepository') &&
                $resource->getRepository() &&
                $resource->getRepository() instanceof RepositoryInterface
            ) {
                $repositoryClass = get_class($resource->getRepository());
                $resource = sprintf($this->_messageTemplate, $resourceClass);
                $queryMessage = ' This resource looks like a `Query`. If you are using `OrmResolver`, ' .
                    'you should create a new policy class for your `%s` class in `src/Policy/`.';
                $resource .= sprintf($queryMessage, $repositoryClass);
            } else {
                $resource = [$resourceClass];
            }
        }

        parent::__construct($resource, $code, $previous);
    }
}
