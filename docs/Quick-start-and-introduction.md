# Quick Start

## Installation

Install the plugin with [composer](https://getcomposer.org/) from your CakePHP
Project's ROOT directory (where the **composer.json** file is located)

```sh
php composer.phar require cakephp/authorization
```

Load the plugin by adding the following statement in your project's `config/bootstrap.php`

```php
Plugin::load('Authorization');
```

## Next Steps

With the plugin installed, you can:

* [Create a Policy](/docs/Policies.md)
* [Choose a Policy Resolver](/docs/Policy-Resolvers.md)
* [Use the Middleware](/docs/Middleware.md)
* [Use the Component](/docs/Component.md)
* [Checking Authorization](/docs/Checking-Authorization.md)
