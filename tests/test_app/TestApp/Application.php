<?php
declare(strict_types=1);

namespace TestApp;

use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Policy\MapResolver;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use Psr\Http\Message\ServerRequestInterface;
use TestApp\Model\Entity\Article;
use TestApp\Policy\ArticlePolicy;

class Application extends BaseApplication implements AuthorizationServiceProviderInterface
{
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        return $middleware;
    }

    public function routes(RouteBuilder $routes): void
    {
    }

    public function bootstrap(): void
    {
        $this->addPlugin('Authorization');
        $this->addPlugin('Bake');
    }

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        return new AuthorizationService($resolver);
    }
}
