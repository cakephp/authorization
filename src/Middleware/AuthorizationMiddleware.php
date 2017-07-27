<?php
namespace App\Middleware;

use Authorization\BouncerInterface;
use Cake\Authorization\Bouncer;
use RuntimeException;

class AuthorizationMiddleware
{

    protected $bouncerClass = Bouncer::class;

    public function __construct($bouncerClass)
    {
        $this->bouncerClass = $bouncerClass;
    }

    public function __invoke($request, $response, $next)
    {
        // Calling $next() delegates control to the *next* middleware
        // In your application's queue.
        $response = $next($request, $response);

        $bouncer = new $this->$bouncerClass(function () use ($request) {
            return $request->getAttribute('identity');
        });

        if (!$bouncer instanceof BouncerInterface) {
            throw new RuntimeException('Invalid Bouncer Object');
        }

        $bouncer->setIdentityResolver(function () use ($request) {
            return $request->getAttribute('identity');
        });

        $response->withAttribute('authorization', $bouncer);

        return $response;
    }
}