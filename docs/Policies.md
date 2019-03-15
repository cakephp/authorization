## Policies

Policies are classes that resolve permissions for a given object. You can create
policies for any class in your application that you wish to apply permissions
checks to.

### Creating Policies

You can create policies in your `src/Policy` directory. Policy classes don't
have a common base class or interface they are expected to implement.
Application classes are then 'resolved' to a matching policy class. See the
[Policy Resolvers](./Policy-Resolvers.md) section for how policies can be
resolved.

Generally you'll want to put your policies in **src/Policy** and use the
``Policy`` class suffix. For now we'll create a policy class for the `Article`
entity in our application.  In **src/Policy/ArticlePolicy.php** put the
following content:

```php
<?php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

class ArticlePolicy
{
}
```

In addition to entities, table objects and queries can have policies resolved.
These objects use the following conventions:


### Writing Policy Methods

The policy class we just created doesn't do much right now. Lets define a method
that allows us to check if a user can update an article.

```php
public function canUpdate(IdentityInterface $user, Article $article)
{
    return $user->id == $article->user_id;
}
```

Policy methods will receive ``null`` for the ``$user`` parameter when handling
unauthencticated users. If you want to automatically fail policy methods for
anonymous users you can use the `IdentityInterface` typehint.

### Policy Result Objects

In addition to booleans, policy methods can return ``Result`` objects which
allow more context to be provided on why the policy passed/failed.

```php
use Authorization\Policy\Result;

public function canUpdate(IdentityInterface $user, Article $article)
{
    if ($user->id == $article->user_id) {
        return new Result(true);
    }
    // Results let you define a 'reason' for the failure.
    return new Result(false, 'not-owner');
}
```

Any return value that is not `true` or a `ResultInterface` object will be
considered a failure.

### Policy Scopes

In addition to policies being able to define pass/fail authorization checks,
they can also define 'scopes'. Scope methods allow you to modify another object
applying authorization conditions. A perfect use case for this is restricting
a list view to the current user:

```php
namespace App\Policy;

class ArticlesPolicy
{
    public function scopeIndex($user, $query)
    {
        return $query->where(['Articles.user_id' => $user->getIdentifier()]);
    }
}
```


### Policy Pre-conditions

In some policies you may wish to apply common checks across all operations in
a policy. This is useful when you need to deny all actions to the provided
resource. To use pre-conditions you need to implement the `BeforePolicyInterface`
in your policy:

```php
namespace App\Policy;

use Authorization\Policy\BeforePolicyInterface;

class ArticlesPolicy implements BeforePolicyInterface
{
    public function before($user, $resource, $action)
    {
        if ($user->getOriginalData()->is_admin) {
            return true;
        }
        // fall through
    }
}
```

Before hooks are expected to return one of three values:

- `true` The user is allowed to proceed with the action.
- `false` The user is not allowed to proceed with the action.
- `null` The before hook did not make a decision, and the authorization method
  will be invoked.
