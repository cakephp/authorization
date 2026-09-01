# AuthorizationComponent

`AuthorizationComponent` exposes convention-based helpers for checking
permissions in controllers. It handles fetching the user and calling `can()` or
`applyScope()` for you. The component depends on the middleware, so make sure
the middleware is already in place.

Load it in your `AppController`:

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

## Automatic Authorization Checks

You can configure the component to authorize actions automatically based on the
controller's default model class and the current action:

```php
$this->Authorization->authorizeModel('index', 'add');
```

You can also mark actions as public by skipping authorization:

```php
$this->loadComponent('Authorization.Authorization', [
    'skipAuthorization' => [
        'login',
    ],
]);
```

The same can be done at runtime, for example in `beforeFilter()`:

```php
$this->Authorization->skipAuthorizationActions('login', 'logout');
```

By default, every action requires authorization when authorization checking is
enabled.

## Checking Authorization

In controller actions or callbacks:

```php
public function edit($id)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article);

    // Rest of edit method
}
```

If you omit the action name, the request action is used. You can override it:

```php
$this->Authorization->authorize($article, 'update');
```

`authorize()` throws `Authorization\Exception\ForbiddenException` when access is
denied. Use `can()` if you want a boolean result:

```php
if ($this->Authorization->can($article, 'update')) {
    // Do something to the article
}
```

## Anonymous Users

Policies decide whether unauthenticated users may access a resource. Both
`can()` and `authorize()` support anonymous users, and your policy methods can
expect `null` for the user parameter when no one is logged in.

## Applying Policy Scopes

You can apply policy scopes through the component:

```php
$query = $this->Authorization->applyScope($this->Articles->find());
```

If there is no logged-in user, `MissingIdentityException` is raised.

If your controller actions map to different policy method names, use
`actionMap`:

```php
$this->Authorization->mapActions([
    'index' => 'list',
    'delete' => 'remove',
    'add' => 'insert',
]);

$this->Authorization
    ->mapAction('index', 'list')
    ->mapAction('delete', 'remove')
    ->mapAction('add', 'insert');
```

Example usage:

```php
public function index()
{
    $query = $this->Articles->find();
    $this->Authorization->applyScope($query);
}

public function delete($id)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article);
}

public function add()
{
    $this->Authorization->authorizeModel();
}
```

## Skipping Authorization

You can also skip authorization inside an action:

```php
public function view($id)
{
    $this->Authorization->skipAuthorization();
}
```
