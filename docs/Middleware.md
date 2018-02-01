# Authorization Middleware

Authorization is applied to your application as a middleware. The
`AuthorizationMiddleware` handles the following responsibilities:

* Decorating the request 'identity' with a decorator that adds the `can` and
  `applyScope` methods.
* Ensuring that authorization has been checked/bypassed in the request.

To use the middleware, update the `middleware` hook of your `Application` with
the following:

```php
// Import the class.
use Authorization\Middleware\AuthorizationMiddleware;

// inside your application's middleware hook.
$middlewareStack->add(new AuthorizationMiddleware($this));
```

By passing your application instance into the middlware, it can invoke the
``authorization`` hook method on your application which should return
a configured `AuthorizationService`. A very simple example would be:

```php
namespace App;

use Authorization\AuthorizationService;
use Authorization\Policy\OrmResolver;
use Cake\Http\BaseApplication;

class Application extends BaseApplication
{
    public function authorization($request)
    {
        $resolver = new OrmResolver();

        return new AuthorizationService($resolver);
    }
}
```

The authorization service requires a policy resolver. See the 
[Policies](./Policies.md) documentation on what resolvers are available and how
to use them.

### Ensuring Authorization is Applied

By default the `AuthorizationMiddleware` will ensure that each request
containing an `identity` also has authorization checked/bypassed. If
authorization is not checked an `AuthorizationRequiredException` will be raised.
This exception is raised *after* your other middleware/controller actions are
complete, so you cannot rely on it to prevent unauthorized access, however it is
a helpful aid during development/testing. You can disable this behavior via an
option:

```php
$middlewareStack->add(new AuthorizationMiddleware($this, [
    'requireAuthorizationCheck' => false
]));
```

### Handling unauthorized requests

By default authorization exceptions thrown by the application are rethrown by the middleware.
You can configure handlers for unauthorized requests and perform custom action, e.g.
redirect the user to the login page.

The built-in handlers are:

* `Exception` - this handler will rethrow the exception, this is a default behavior of the middleware.
* `Redirect` - this handler will redirect the request to the provided URL.
* `CakeRedirect` - redirect handler with support for CakePHP Router.

Both redirect handlers share the same configuration options:

* `url` - URL to redirect to (`CakeRedirect` supports CakePHP Router syntax).
* `exceptions` - a list of exception classes that should be redirected. By default only `MissingIdentityException` is redirected.
* `queryParam` - the accessed request URL will be attached to the redirect URL query parameter (`redirect` by default).
* `statusCode` - HTTP status code of a redirect, `302` by default.

For example:

```php
$middlewareStack->add(new AuthorizationMiddleware($this, [
    'unauthorizedHandler' => [
        'className' => 'Authorization.Redirect',
        'url' => '/users/login',
        'queryParam' => 'redirectUrl',
        'exceptions' => [
            MissingIdentityException::class,
            OtherException::class,
        ],
    ],
]));
```

You can also add your own handler. Handlers should implement `Authorization\Middleware\UnauthorizedHandler\HandlerInterface`,
be suffixed with `Handler` suffix and reside under your app's or plugin's 
`Middleware\UnauthorizedHandler` namespace.

Configuration options are passed to the handler's `handle()` method as the last parameter.

Handlers catch only those exceptions which extend `Authorization\Exception\Exception` class.
