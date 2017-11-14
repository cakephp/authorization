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
     * @param string|\Cake\Authorization\BouncerInterface
     */
    public function __construct($bouncer)
    {
        $this->bouncer = $bouncer;
    }

    /**
     * Invoke
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @param \Psr\Http\Message\ResponseInterface $response Response
     * @param callable $next
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
