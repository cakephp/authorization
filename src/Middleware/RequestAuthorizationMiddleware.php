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
use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
use Authorization\IdentityInterface;
use Cake\Core\InstanceConfigTrait;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Request Authorization Middleware
 *
 * This MUST be added after the Authorization, Authentication and
 * RoutingMiddleware in the Middleware Queue!
 *
 * This middleware is useful when you want to authorize your requests, for example
 * each controller and action, against a role based access system or any other
 * kind of authorization process that controls access to certain actions.
 */
class RequestAuthorizationMiddleware
{

    use InstanceConfigTrait;

    /**
     * Default Config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'authorizationAttribute' => 'authorization',
        'identityAttribute' => 'identity',
        'redirectUrl' => null,
        'unauthorizedHandler' => null,
        'missingIdentityHandler' => null,
        'allowAccessWhenIdentityIsMissing' => false,
        'canMethod' => 'access'
    ];

    /**
     * Constructor
     *
     * @var array $config Configuration options
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Gets the authorization service from the request attribute
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
     * @return \Authorization\AuthorizationServiceInterface
     */
    protected function getServiceFromRequest(ServerRequestInterface $request)
    {
        $serviceAttribute = $this->getConfig('authorizationAttribute');
        $service = ($request->getAttribute($serviceAttribute));

        if (!$service instanceof AuthorizationServiceInterface) {
            $errorMessage = __CLASS__ . ' could not find the authorization service in the request attribute. '
                . 'Make sure you added the AuthorizationMiddleware before this middleware or that you '
                . 'somehow else added the service to the requests `' . $serviceAttribute . '` attribute.';

            throw new RuntimeException($errorMessage);
        }

        return $service;
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
        $service = $this->getServiceFromRequest($request);
        $identity = $request->getAttribute($this->getConfig('identityAttribute'));

        if ($identity instanceof IdentityInterface) {
            if (!$service->can($identity, $this->getConfig('canMethod'), $request)) {
                $response = $this->handleUnauthorized($identity, $request, $response);
            }
        } else {
            $response = $this->handleMissingIdentity($request, $response);
        }

        return $next($request, $response);
    }

    /**
     * Handles the case no identity is present
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
     * @param \Psr\Http\Message\ResponseInterface $response Response.
     * @return \Psr\Http\Message\ResponseInterface $response Response.
     */
    public function handleMissingIdentity($request, $response)
    {
        $handler = $this->getConfig('missingIdentityHandler');
        if (is_callable($handler)) {
            return $handler($request, $response);
        }

        if ($this->getConfig('allowAccessWhenIdentityIsMissing')) {
            return $response;
        }

        $url = $this->getConfig('redirectUrl');
        if (!empty($url)) {
            return $this->redirect($response, $url);
        }

        throw new MissingIdentityException();
    }

    /**
     * Handles the case no identity is present or it is unauthorized
     *
     * @param \Authorization\IdentityInterface;
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request.
     * @param \Psr\Http\Message\ResponseInterface $response Response.
     * @return \Psr\Http\Message\ResponseInterface $response Response.
     */
    public function handleUnauthorized($identity, $request, $response)
    {
        $handler = $this->getConfig('unauthorizedHandler');
        if (is_callable($handler)) {
            return $handler($identity, $request, $response);
        }

        $url = $this->getConfig('redirectUrl');
        if (!empty($url)) {
            return $this->redirect($response, $url);
        }

        throw new ForbiddenException();
    }

    /**
     * Handles the redirect
     *
     * @param \Psr\Http\Message\ResponseInterface $response Response.
     * @param string|array $url URL to redirect to
     * @return \Psr\Http\Message\ResponseInterface $response Response.
     */
    public function redirect(ResponseInterface $response, $url)
    {
        if (is_array($url)) {
            $url = Router::url($url);
        }

        return $response->withStatus(302)
            ->withHeader('Location', $url);
    }
}
