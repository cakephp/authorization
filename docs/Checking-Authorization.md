# Checking Authorization

Once you have applied the [Middleware](./Middleware.md) to your
application and added an `identity` to the request, you can start checking
authorization. The middleware will wrap your request `identity` with an
`IdentityDecorator` that adds authorization related methods:

```php
// Get the identity from the request
$user = $this->request->getAttribute('identity');

// Check authorization on $article
if ($user->can('delete', $article)) {
    // Do delete operation
}
```

You can also use the `identity` to apply scopes:

```php
// Get the identity from the request
$user = $this->request->getAttribute('identity');

// Apply permission conditions to a query
$query = $user->applyScope('index', $query);
```

The `IdentityDecorator` will forward all method calls, array access, and
property access to the decorated identity object. If you need to access the
underlying identity directly use `getOriginalData()`:

```php
$originalUser = $user->getOriginalData();
```

You can pass the `$user` into your models, services or templates allowing you to
check authorization anywhere in your application easily. See the [identity
decorator](./Middleware.md#identity-decorator) section for how to customize or
replace the default decorator.

The [AuthorizationComponent](./Component.md) can be used in controller actions
to streamline authorization checks that raise exceptions on failure.
