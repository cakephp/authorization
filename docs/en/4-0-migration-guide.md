# 4.0 Migration Guide

Authorization 4.0 supports CakePHP 6.

## Requirements

Authorization 4.0 requires CakePHP 6.0 and PHP 8.4.0 or higher.

## Container Classes Moved

`Cake\Core\Container` and `Cake\Core\ContainerInterface` moved to the
`Cake\Container` namespace:

- `Cake\Core\Container` is now `Cake\Container\Container`
- `Cake\Core\ContainerInterface` is now `Cake\Container\ContainerInterface`

`AuthorizationMiddleware`, `MapResolver`, and `OrmResolver` accept a container
as their constructor argument, and now type hint `Cake\Container\ContainerInterface`.
Update your imports, including where you subclass these classes and override
their constructors.

## Protected Properties Renamed

CakePHP 6 dropped the underscore prefix from protected properties. The
following properties were renamed. This only affects applications that
subclass these classes:

- `AuthorizationComponent`, `AuthorizationMiddleware`, and
  `RequestAuthorizationMiddleware`: `$_defaultConfig` is now `$defaultConfig`
- `AuthorizationComponent`: `$_config`, from `InstanceConfigTrait`, is now
  `$config`
- `MissingIdentityException`, `AuthorizationRequiredException`,
  `ForbiddenException`, `MissingMethodException`, and `MissingPolicyException`:
  `$_defaultCode` and `$_messageTemplate` are now `$defaultCode` and
  `$messageTemplate`

The exception properties must match the names used by
`Cake\Http\Exception\HttpException`, otherwise the status codes and message
templates defined by these classes are ignored.

## Fluent Methods Declare Static Return Types

The following methods now declare a `static` return type. Subclasses that
override them must declare a compatible return type:

- `AuthorizationComponent::skipAuthorization()`
- `AuthorizationComponent::mapAction()`
- `AuthorizationComponent::mapActions()`
- `AuthorizationComponent::authorizeModel()`
- `MapResolver::map()`
- `ResolverCollection::add()`

## Constructor Changes

`IdentityDecorator::__construct()` renamed its first parameter from `$service`
to `$authorization` to match the promoted property. Positional arguments are
unaffected. Calls using named arguments must be updated.

`AuthorizationService`, `IdentityDecorator`, `AuthorizationMiddleware`,
`ForbiddenException`, `MapResolver`, `OrmResolver`, and `Result` now declare
their constructor-assigned properties as promoted properties. The visibility
and default values of these properties are unchanged.

## Bake Command

`Authorization\Command\PolicyCommand` targets the Bake 6 API:

- `templateData()` no longer accepts an `Arguments` instance, it reads the name
  and type from the command's stored arguments.
- `bakeTest()` now only accepts a class name.
- `buildOptionParser()` is now `protected`.

Only applications that subclass `PolicyCommand` are affected. The
`bin/cake bake policy` command line interface is unchanged.
