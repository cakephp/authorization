クイック スタート
###########

インストール
============

プラグインをインストールするには、CakePHPから `composer <https://getcomposer.org/>`__ を
プロジェクトのルートディレクトリ (**composer.json** がある場所) で使用します。


.. code-block:: shell

    php composer.phar require "cakephp/authorization:^2.0"
    
Authorization Plugin(認可プラグイン)のバージョン2はCakePHP4に対応しています。

プラグインを読み込むにはApplication.phpに下記の一業を追加します。
``src/Application.php``
::

    $this->addPlugin('Authorization');

はじめに
===============

Authorization pluginは、ミドルウェア層としてアプリケーションに組み込まれ、
オプションのコンポーネントで認可の確認を容易に行え行えます。
はじめに **src/Application.php** に以下のクラス使用を明示してください。::

    use Authorization\AuthorizationService;
    use Authorization\AuthorizationServiceInterface;
    use Authorization\AuthorizationServiceProviderInterface;
    use Authorization\Middleware\AuthorizationMiddleware;
    use Authorization\Policy\OrmResolver;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

Applicationに ``AuthorizationServiceProviderInterface`` を追加してください::

    class Application extends BaseApplication implements AuthorizationServiceProviderInterface

そして、Applicationの ``middleware()`` を以下のようにします(AuthorizationMiddlewareを加える)::

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Middleware provided by CakePHP
        $middlewareQueue->add(new ErrorHandlerMiddleware(Configure::read('Error')))
            ->add(new AssetMiddleware())
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())

            // If you are using Authentication it should be *before* Authorization.
            ->add(new AuthenticationMiddleware($this));

            // Add the AuthorizationMiddleware *after* routing, body parser
            // and authentication middleware.
            ->add(new AuthorizationMiddleware($this));

        return $middlewareQueue();
    }

``AuthorizationMiddleware`` は ``AuthenticationMiddleware`` の **後** に置く必要があります。
これにより、認可の確認で使用する ``identity`` が使用できるようになります。

``AuthorizationMiddleware`` はリクエスト処理が開始した際に、フックメソッドを呼び出します。
このフックメソッドで、使用したい ``AuthorizationService`` を定義できます。
以下のメソッドを **src/Application.php** に追加してください。::

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new OrmResolver();

        return new AuthorizationService($resolver);
    }

これは、ORMエンティティとPolicyクラスをマッチングする、基本的な :doc:`/policy-resolvers` を設定するものです。

次に、　``AppController`` に ``AuthorizationComponent`` を加えます。
**src/Controller/AppController.php** の ``initialize()`` に下記を追加してください。::

    $this->loadComponent('Authorization.Authorization');

:doc:`/component` を読み込むことで、以下のような認可チェックを行うことができます。

    public function edit($id = null)
    {
        $article = $this->Article->get($id);
        $this->Authorization->authorize($article, 'update');

        // Rest of action
    }

``authorize`` を呼び出すことで、 :doc:`/policies` を使用して、アクセス制御ルールを強制することができます。
:doc:`identity stored in the request <checking-authorization>` を使えばどこでも権限を確認することができます。


より詳しく
===============

* :doc:`/policies`
* :doc:`/policy-resolvers`
* :doc:`/middleware`
* :doc:`/component`
* :doc:`/checking-authorization`
* :doc:`/request-authorization-middleware`
