# Request Authorization Middleware

This middleware is useful when you want to authorize your requests, for example each controller and action, against a role based access system or any other kind of authorization process that controls access to certain actions.

This **must** be added after the Authorization, Authentication and RoutingMiddleware in the Middleware Queue!

The logic of handling the request authorization will be implemented in the request policy. You can add all your logic there or just pass the information from the request into an ACL or RBAC implementation.

## Using it

Create a policy for handling the request object. The plugin ships with an interface here to implement.

```php
namespace App\Policy;

use Phauthentic\Authorization\IdentityInterface;
use Phauthentic\Authorization\Policy\RequestPolicyInterface;
use Phauthentic\Authorization\Policy\Result;
use Phauthentic\Authorization\Policy\ResultInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestPolicy implements RequestPolicyInterface
{
    /**
     * Method to check if the request can be accessed
     *
     * @param \Phauthentic\Authorization\IdentityInterface|null Identity
     * @param \Cake\Http\ServerRequest $request Server Request
     * @return \Phauthentic\Authorization\Policy\ResultInterface
     */
    public function canAccess(?IdentityInterface $identity, ServerRequestInterface $request): ResultInterface
    {
        if ($request->getParam('controller') === 'Articles'
            && $request->getParam('action') === 'index'
        ) {
            return new Result(true);
        }

        return new Result(false);
    }
}
```

Map the request class to the policy inside `Application::getAuthorizationService()`:

```php
use App\Policy\RequestPolicy;
use Phauthentic\Authorization\AuthorizationService;
use Phauthentic\Authorization\Policy\MapResolver;
use Psr\Http\Message\ServerRequestInterface;

$mapResolver = new MapResolver();
$mapResolver->map(ServerRequest::class, RequestPolicy::class);

return new AuthorizationService($resolver);
```

In your `Application.php` make sure you're loading the RequestAuthorizationMiddleware **after** the AuthorizationMiddleware!

```php
// Add authorization (after authentication if you are using that plugin too).
$middleware->add(new AuthorizationMiddleware($this));
$middleware->add(new RequestAuthorizationMiddleware());
```
