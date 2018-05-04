# Request Authorization Middleware

This middleware is useful when you want to authorize your requests, for example each controller and action, against a role based access system or any other kind of authorization process that controls access to certain actions.

This MUST be added after the Authorization, Authentication and RoutingMiddleware in the Middleware Queue!

This middleware is useful when you want to authorize your requests, for example each controller and action, against a role based access system or any other kind of authorization process that controls access to certain actions.

## Using it

First map the request class to a custom policy.

```php
use App\Policy\RequestPolicy;
use Authorization\Policy\MapResolver;
use Cake\Http\ServerRequest;

$map = new MapResolver();
$map->map(ServerRequest::class, RequestPolicy::class);
```
