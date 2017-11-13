<?php
namespace Authorization\Middleware;

use Authorization\BouncerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IdentityResolverMiddleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /* @var $bouncer BouncerInterface */
        $bouncer = $request->getAttribute('authorization');

        $bouncer->setIdentityResolver(function() use ($request) {
            return $request->getAttribute('identity');
        });

        return $next($request, $response);
    }
}