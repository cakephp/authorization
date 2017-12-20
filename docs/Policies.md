## Policies

Policies are classes that resolve permissions for a given object. You can create
policies for any class in your application that you wish to apply permissions
checks to.

### Creating Policies

You can create policies in your `src/Policy` directory. Policy classes for ORM
objects follow the following conventions.

### Policy conventions for ORM classes

1. Policy classes use the `Policy` suffix.
2. Policy classes live in the `App\Policy` namespace or `$plugin\Policy`.

The default ORM resolver uses the following conventions for policy classes:

1. The entity classname is used to generate the policy class name. e.g
   `App\Model\Entity\Article` will map to `App\Policy\ArticlePolicy`.
2. Plugin entities will first check for an application policy e.g
   `App\Policy\Blog\ArticlePolicy` for `Bookmarks\Model\Entity\Article`.
3. If no application override policy can be found, a plugin policy will be
   checked. e.g. `Blog\Policy\ArticlePolicy`.
4. If no policy can be found an exception will be raised.

For now we'll create a policy class for the `Article` entity in our application.
In **src/Policy/ArticlePolicy.php** put the following content:

```php
<?php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;

class ArticlePolicy
{
}
```

### Writing Policy Methods

The policy class we just created doesn't do much right now. Lets define a method
that allows us to check if a user can update an article.

```php
public function canUpdate(IdentityInterface $user, Article $article)
{
    return $user->id == $article->user_id;
}
```

Policy methods must return `true` to indicate success. All other values will be
interpreted as failure.

### Policy Preconditions

In some policies you may wish to apply common checks across all operations in
a policy. This is useful when you need to deny all actions to the provided
resource.


# Policy Resolvers

Mapping resource objects to their respective policy classes is a behavior
handled by a policy resolver. We provide a few resolvers to get you started, but
you can create your own resolver by implementing the
`Authorization\Policy\ResolverInterface`. The built-in resolvers are:

* `MapResolver` allows you to map resource names to their policy class names.
* `OrmResolver` applies conventions based policy resolution for common ORM
  objects.
* `ResolverCollection` allows you to aggregate multiple resolvers together,
  searching them sequentially.
