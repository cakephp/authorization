# CakePHP Authorization

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/travis/cakephp/authorization/master.svg?style=flat-square)](https://travis-ci.org/cakephp/authorization)
[![Coverage Status](https://img.shields.io/codecov/c/github/cakephp/authorization.svg?style=flat-square)](https://codecov.io/github/cakephp/authorization)

Authorization stack for the CakePHP framework.

This branch is for CakePHP 3.x.

## Authorization not Authentication

This plugin intends to provide a framework around authorization and access
control. Authentication is a [separate 
concern](https://en.wikipedia.org/wiki/Separation_of_concerns) that has been
packaged into a separate [authentication plugin](https://github.com/cakephp/authentication).

## Installation

You can install this plugin into your CakePHP application using 
[composer](https://getcomposer.org):

```
php composer.phar require cakephp/authorization
```

Load the plugin by adding the following statement in your project's
`src/Application.php`:
```php
$this->addPlugin('Authorization');
```
Prior to 3.6.0
```php
Plugin::load('Authorization');
```

## Documentation

 * [Quick Start and Introduction to the basics](docs/Quick-start-and-introduction.md)
 * [Policies](docs/Policies.md)
 * [Policy Resolver](docs/Policy-Resolvers.md)
 * [Middleware](docs/Middleware.md)
 * [Component](docs/Component.md)
 * [Checking Authorization](docs/Checking-Authorization.md)
