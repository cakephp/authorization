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
        $bouncer = $this->bouncer;

        if (is_string($bouncer)) {
            $bouncer = new $bouncer();
        }

        if (!$bouncer instanceof BouncerInterface) {
            throw new RuntimeException('Invalid Bouncer Object');
        }

        $response = $response->withAttribute('authorization', $bouncer);

        return $next($request, $response);
    }
}
