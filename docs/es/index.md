# Inicio Rápido

## Instalación

Instale el plugin con [Composer](https://getcomposer.org/) desde el directorio
raíz de su proyecto CakePHP:

```bash
php composer.phar require "cakephp/authorization:^4.0"
```

Cargue el plugin en `src/Application.php`:

```php
$this->addPlugin('Authorization');
```

## Empezando

Authorization se integra como middleware y, opcionalmente, como componente.

```php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ServerRequestInterface;
```

```php
class Application extends BaseApplication implements AuthorizationServiceProviderInterface
```

```php
$middlewareQueue->add(new AuthorizationMiddleware($this));
```

```php
public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $resolver = new OrmResolver();

    return new AuthorizationService($resolver);
}
```

Después, cargue el componente:

```php
$this->loadComponent('Authorization.Authorization');
```

## Otras lecturas

- [Policy](./policies)
