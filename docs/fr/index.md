# Prise en main rapide

## Installation

Installez le plugin avec [Composer](https://getcomposer.org/) depuis le
répertoire racine de votre projet CakePHP, là où se trouve `composer.json` :

```bash
php composer.phar require "cakephp/authorization:^4.0"
```

La version 4 du plugin Authorization est compatible avec CakePHP 6.

Chargez le plugin dans `src/Application.php` :

```php
$this->addPlugin('Authorization');
```

## Pour commencer

Le plugin Authorization s'intègre comme middleware et, de manière optionnelle,
comme composant pour simplifier les vérifications d'autorisation.

Dans `src/Application.php`, ajoutez les imports suivants :

```php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ServerRequestInterface;
```

Ajoutez `AuthorizationServiceProviderInterface` aux interfaces implémentées par
votre classe `Application` :

```php
class Application extends BaseApplication implements AuthorizationServiceProviderInterface
```

Puis adaptez la méthode `middleware()` :

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue->add(new ErrorHandlerMiddleware(Configure::read('Error')))
        ->add(new AssetMiddleware())
        ->add(new RoutingMiddleware($this))
        ->add(new BodyParserMiddleware())
        ->add(new AuthenticationMiddleware($this))
        ->add(new AuthorizationMiddleware($this));

    return $middlewareQueue;
}
```

`AuthorizationMiddleware` doit être ajouté après `AuthenticationMiddleware`
afin que la requête contienne bien une `identity`.

Ajoutez ensuite la méthode suivante :

```php
public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $resolver = new OrmResolver();

    return new AuthorizationService($resolver);
}
```

Cela configure les [résolveurs de policy](./policy-resolvers) de base pour
faire correspondre les entités ORM à leurs classes de policy.

Chargez ensuite `AuthorizationComponent` dans
`src/Controller/AppController.php` :

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

Le [component](./component) permet d'autoriser facilement une ressource dans
une action :

```php
public function edit($id = null)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article, 'update');
}
```

En appelant `authorize()`, vous utilisez vos [policies](./policies) pour
appliquer les règles d'accès de l'application. Vous pouvez aussi vérifier des
permissions partout où vous avez accès à [l'identity de la requête](./checking-authorization).

## Pour aller plus loin

- [Policies](./policies)
- [Résolveurs de Policy](./policy-resolvers)
- [Middleware Authorization](./middleware)
- [AuthorizationComponent](./component)
- [Vérifier une Autorisation](./checking-authorization)
- [Middleware d'Autorisation de Requête](./request-authorization-middleware)
