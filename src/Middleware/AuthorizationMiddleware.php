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
namespace Authorization\Middleware;

use Authorization\AuthorizationServiceInterface;
use Authorization\Exception\AuthorizationRequiredException;
use Authorization\IdentityDecorator;
use Authorization\IdentityInterface;
use Cake\Core\HttpApplicationInterface;
use Cake\Core\InstanceConfigTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Authorization Middleware.
 *
 * Injects the authorization service and decorated identity objects into the request object as attributes.
 */
class AuthorizationMiddleware
{
    use InstanceConfigTrait;

    /**
     * Default config.
     *
     * - `identityDecorator` Identity decorator class name or a callable.
     *   Defaults to IdentityDecorator
     * - `identityAttribute` Attribute name the identity is stored under.
     *   Defaults to 'identity'
     * - `requireAuthorizationCheck` When true the middleware will raise an exception
     *   if no authorization checks were done. This aids in ensuring that all actions
     *   check authorization. It is intended as a development aid and not to be relied upon
     *   in production. Defaults to `true`.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'identityDecorator' => IdentityDecorator::class,
        'identityAttribute' => 'identity',
        'requireAuthorizationCheck' => true
    ];

    /**
     * Authorization service or application instance.
     *
     * @var \Authorization\AuthorizationServiceInterface|\Cake\Core\HttpApplicationInterface
     */
    protected $subject;

    /**
     * Constructor.
     *
     * @param \Authorization\AuthorizationServiceInterface|\Cake\Core\HttpApplicationInterface $subject Authorization service or application instance.
     * @param array $config Config array.
     * @throws InvalidArgumentException
     */
    public function __construct($subject, array $config = [])
    {
        if (!$subject instanceof AuthorizationServiceInterface && !$subject instanceof HttpApplicationInterface) {
            $expected = implode('` or `', [
                AuthorizationServiceInterface::class,
                HttpApplicationInterface::class
            ]);
            $type = is_object($subject) ? get_class($subject) : gettype($subject);
            $message = sprintf('Subject must be an instance of `%s`, `%s` given.', $expected, $type);

            throw new InvalidArgumentException($message);
        }

        $this->subject = $subject;
        $this->setConfig($config);
    }

    /**
     * Callable implementation for the middleware stack.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
     * @param \Psr\Http\Message\ResponseInterface $response Response.
     * @param callable $next The next middleware to call.
     * @return ResponseInterface A response.
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $service = $this->getAuthorizationService($request, $response);
        $request = $request->withAttribute('authorization', $service);

        $attribute = $this->getConfig('identityAttribute');
        $identity = $request->getAttribute($attribute);

        if ($identity === null) {
            return $next($request, $response);
        }

        $identity = $this->buildIdentity($service, $identity);

        $request = $request->withAttribute($attribute, $identity);
        $response = $next($request, $response);
        if ($this->getConfig('requireAuthorizationCheck') && !$service->authorizationChecked()) {
            throw new AuthorizationRequiredException(['url' => $request->getRequestTarget()]);
        }

        return $response;
    }

    /**
     * Returns AuthorizationServiceInterface instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
     * @param \Psr\Http\Message\ResponseInterface $response Response.
     * @return \Authorization\AuthorizationServiceInterface
     * @throws \RuntimeException When authorization method has not been defined.
     */
    protected function getAuthorizationService($request, $response)
    {
        if ($this->subject instanceof AuthorizationServiceInterface) {
            return $this->subject;
        }

        $method = 'authorization';
        if (!method_exists($this->subject, $method)) {
            $message = sprintf('Method `%s` has not been defined in your `Application` class.', $method);
            throw new RuntimeException($message);
        }

        $service = $this->subject->$method($request, $response);

        if (!$service instanceof AuthorizationServiceInterface) {
            throw new RuntimeException(sprintf(
                'Invalid service returned from `%s` method. `%s` does not implement `%s`.',
                $method,
                is_object($service) ? get_class($service) : gettype($service),
                AuthorizationServiceInterface::class
            ));
        }

        return $service;
    }

    /**
     * Builds the identity object.
     *
     * @param \Authorization\AuthorizationServiceInterface $service Authorization service.
     * @param \ArrayAccess|array $identity Identity data
     * @return \Authorization\IdentityInterface
     */
    protected function buildIdentity(AuthorizationServiceInterface $service, $identity)
    {
        $class = $this->getConfig('identityDecorator');

        if (is_callable($class)) {
            $identity = $class($service, $identity);
        } else {
            if (!$identity instanceof IdentityInterface) {
                $identity = new $class($service, $identity);
            }
        }

        if (!$identity instanceof IdentityInterface) {
            throw new RuntimeException(sprintf(
                'Invalid identity returned by decorator. `%s` does not implement `%s`.',
                is_object($identity) ? get_class($identity) : gettype($identity),
                IdentityInterface::class
            ));
        }

        return $identity;
    }
}
