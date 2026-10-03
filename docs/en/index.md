# Quick Start

## Installation

Install the plugin with [Composer](https://getcomposer.org/) from your CakePHP
project root, where `composer.json` is located:

```bash
php composer.phar require "cakephp/authorization:^4.0"
```

Authorization 4.x is compatible with CakePHP 6.

Load the plugin in `src/Application.php`:

```php
$this->addPlugin('Authorization');
```

## Getting Started

The Authorization plugin integrates into your application as middleware and,
optionally, as a component that makes authorization checks easier in
controllers.

In `src/Application.php`, add the required imports:

```php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ServerRequestInterface;
```

Implement `AuthorizationServiceProviderInterface` on your application class:

```php
class Application extends BaseApplication implements AuthorizationServiceProviderInterface
```

Then register the middleware:

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue->add(new ErrorHandlerMiddleware(Configure::read('Error')))
        ->add(new AssetMiddleware())
        ->add(new RoutingMiddleware($this))
        ->add(new BodyParserMiddleware())

        // If you use Authentication it must come before Authorization.
        ->add(new AuthenticationMiddleware($this))

        // Add Authorization after routing, body parsing, and authentication.
        ->add(new AuthorizationMiddleware($this));

    return $middlewareQueue;
}
```

`AuthorizationMiddleware` must be added after authentication so the request
contains an `identity` for authorization checks.

The middleware calls a hook on your application to obtain the authorization
service. Add this method to `src/Application.php`:

```php
public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $resolver = new OrmResolver();

    return new AuthorizationService($resolver);
}
```

This configures the basic [policy resolvers](/policy-resolvers) that map ORM
entities to policy classes.

Next, load the component in `src/Controller/AppController.php`:

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

With the [AuthorizationComponent](/component) loaded, you can authorize
resources per action:

```php
public function edit($id = null)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article, 'update');

    // Rest of action
}
```

Calling `authorize()` lets your [policies](/policies) enforce access-control
rules. You can also check permissions anywhere you have access to the
[request identity](/checking-authorization).

## Further Reading

- [Policies](/policies)
- [Policy Resolvers](/policy-resolvers)
- [Authorization Middleware](/middleware)
- [AuthorizationComponent](/component)
- [Checking Authorization](/checking-authorization)
- [Request Authorization Middleware](/request-authorization-middleware)
