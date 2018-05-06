# Request Authorization Middleware

This middleware is useful when you want to authorize your requests, for example each controller and action, against a role based access system or any other kind of authorization process that controls access to certain actions.

This **must** be added after the Authorization, Authentication and RoutingMiddleware in the Middleware Queue!

This middleware is useful when you want to authorize your requests, for example each controller and action, against a role based access system or any other kind of authorization process that controls access to certain actions.

The logic of handling the request authorization will be implemented in the request policy. You can add all your logic there or just pass the information from the request into an ACL or RBAC implementation.

## Using it

Create a policy for handling the request object.

```php
class RequestPolicy
{
    /**
     * Method to check if the request can be accessed
     *
     * @param null|\Authorization\IdentityInterface Identity
     * @param \Cake\Http\ServerRequest $request Server Request
     * @return bool
     */
    public function canAccess($identity, ServerRequest $request)
    {
        if ($request->getParam('controller') === 'Articles'
            && $request->getParam('action') === 'index'
        ) {
            return true;
        }

        return false;
    }
}

```

Map the request class to the policy.

```php
use App\Policy\RequestPolicy;
use Authorization\Policy\MapResolver;
use Cake\Http\ServerRequest;

$map = new MapResolver();
$map->map(ServerRequest::class, RequestPolicy::class);
```

In your `Application.php` make sure you're loading the RequestAuthorizationMiddleware **after** the AuthorizationMiddleware! 

```php
// Add authorization (after authentication if you are using that plugin too).
$middleware->add(new AuthorizationMiddleware($this));
$middleware->add(new RequestAuthorizationMiddleware())
```
