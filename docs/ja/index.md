# クイックスタート

## インストール

CakePHP プロジェクトのルートディレクトリで
[Composer](https://getcomposer.org/) を使ってプラグインを追加します。

```bash
php composer.phar require "cakephp/authorization:^4.0"
```

Authorization 4.x は CakePHP 6 に対応しています。

`src/Application.php` でプラグインを読み込みます。

```php
$this->addPlugin('Authorization');
```

## はじめに

Authorization プラグインはミドルウェアとして組み込まれ、必要に応じて
コンポーネントとしても利用できます。

```php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ServerRequestInterface;
```

`Application` に `AuthorizationServiceProviderInterface` を追加します。

```php
class Application extends BaseApplication implements AuthorizationServiceProviderInterface
```

`middleware()` に Authorization を追加します。

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

`AuthorizationMiddleware` は `AuthenticationMiddleware` の後に置く必要が
あります。

```php
public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $resolver = new OrmResolver();

    return new AuthorizationService($resolver);
}
```

これは [ポリシーリゾルバー](./policy-resolvers) を構成するための基本設定です。

`AppController` で component を読み込みます。

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}
```

```php
public function edit($id = null)
{
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article, 'update');
}
```

`authorize()` を使うことで、[Policies](./policies) によるアクセス制御を
適用できます。

## より詳しく

- [Policies](./policies)
- [ポリシーリゾルバー](./policy-resolvers)
- [認可ミドルウェア](./middleware)
- [AuthorizationComponent](./component)
- [認可の確認](./checking-authorization)
- [リクエスト認証ミドルウェア](./request-authorization-middleware)
