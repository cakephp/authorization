<?php
namespace App\Middleware;

use Authorization\BouncerInterface;
use Cake\Authorization\Bouncer;
use RuntimeException;

/**
 * Authorization Middleware
 *
 * Injects the bouncer object into the request object as attribute
 */
class AuthorizationMiddleware
{

    /**
     * Bouncer class or instance
     *
     * @var string||\Cake\Authorization\BouncerInterface
     */
    protected $bouncer = Bouncer::class;

    /**
     * Constructor
     *
     * @param string|\Cake\Authorization\BouncerInterface|null
     */
    public function __construct($bouncer = null)
    {
        $this->bouncer = $bouncer;
    }

    /**
     * Invoke
     *
     * @param \Cake\Http\ServerRequest $request Request
     * @param \Cake\Http\Response $response Response
     * @param callable $next Next middleware
     * @return callable
     */
    public function __invoke($request, $response, $next)
    {
        $response = $next($request, $response);
        $bouncer = $this->bouncer;

        if (is_string($bouncer)) {
            $bouncer = new $this->$bouncer();

            if (!class_exists('Cake\Authorization\Identity')) {
                trigger_error('The AuthorizationMiddleware requires that you are using the Cake\Authorization plugin.', E_WARNING);
            }

            $bouncer->setIdentityResolver(function () use ($request) {
                return $request->getAttribute('identity');
            });
        }

        if (!$bouncer instanceof BouncerInterface) {
            throw new RuntimeException('Invalid Bouncer Object');
        }

        $response = $response->withAttribute('authorization', $bouncer);

        return $response;
    }
}
