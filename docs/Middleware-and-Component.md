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

You can bypass the authorization check for individual requests by calling method
`skipAuthorization()` on `AuthorizationService` instance:

```php
$request->getAttribute('authorization')->skipAuthorization();
```

# AuthorizationComponent

The `AuthorizationComponent` exposes a few conventions based helper methods for
checking permissions from your controllers. It abstracts getting the user and
calling the `can` or `applyScope` methods. Using the AuthorizationComponent
requires use of the Middleware, so make sure it is applied as well. To use the
component, first load it:

```php
// In your AppController
public function initialize()
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

### Automatic authorization checks

By default `AuthorizationComponent` will attempt to automatically apply
authorization based on the controller's default model class and current action
name. You can disable this behavior entirely or configure it for individual actions 
using the `authorizeModel` option.


In the following example all actions will be authorized except the `index` action:

```php
$this->loadComponent('Authorization.Authorization', [
    'authorizeModel' => [
        'index' => false,
    ]
];
```

You can control default check for all actions using a *wildcard character* `*`.
In the following example only `add` action would be authorized automatically:

```php
$this->loadComponent('Authorization.Authorization', [
    'authorizeModel' => [
        '*' => false,
        'add' => true,
    ]
];
```

You can also configure actions to skip authorization. This will make actions *public*,
accessible to all users. By default all actions require authorization and
`AuthorizationRequiredException` will be thrown if authorization checking is enabled.

Authorization can be skipped for individual actions:

```php
$this->loadComponent('Authorization.Authorization', [
    'skipAuthorization' => [
        'login' => true,
    ]
];
```

Authorization bypass can be configured for all actions as well:

```php
$this->loadComponent('Authorization.Authorization', [
    'skipAuthorization' => [
        '*' => true,
    ]
];
```

### Component Usage

In your controller actions or callback methods you can check authorization using
the component:

```php
// In the Articles Controller.
public function edit($id)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article);
    // Rest of the edit method.
}
```

Above we see an article being authorized for the current user. By default the current
request's `action` is used for the policy method. You can choose
a policy method to use if necessary:

```php
// Use a policy method that doesn't match the current controller action.
$this->Authorization->authorize($article, 'update');
```

You can also apply policy scopes using the component:

```php
$query = $this->Authorization->applyScope($this->Articles->find());
```

If you want to map actions to different authorization methods use the `actionMap` option:

```php
$this->loadComponent('Authorization.Authorization', [
    'actionMap' => [
        'update' => 'modify',
        'delete' => 'remove',
    ]
];
```

Authorization can also be skipped manually, e.g. from action body:

```php
$this->Authorization->skipAuthorization();
```
