認可ミドルウェア
########################

認可はミドルウェアとしてアプリケーションに適用されます。
``AuthorizationMiddleware`` は次のような役割があります。:

* リクエストの '識別要素' を必要に応じて ``can``, ``canResult``, ``applyScope`` メソッドで装飾します。
* リクエストの確認/回避を確実にする

アプリケーションにミドルウェアを反映させるにはApplicationクラスに ``AuthorizationServiceProviderInterface`` を追加します。
ミドルウェアをキューに追加するのもお忘れなく。
基本的な例::

    namespace App;
    use Authorization\AuthorizationService;
    use Authorization\AuthorizationServiceProviderInterface;
    use Authorization\Middleware\AuthorizationMiddleware;
    use Authorization\Policy\OrmResolver;
    use Cake\Http\BaseApplication;
    class Application extends BaseApplication implements AuthorizationServiceProviderInterface
    {
        public function getAuthorizationService(ServerRequestInterface $request, ResponseInterface $response)
        {
            $resolver = new OrmResolver();
            return new AuthorizationService($resolver);
        }
        public function middleware($middlewareQueue)
        {
            // other middleware
            $middlewareQueue->add(new AuthorizationMiddleware($this));
            return $middlewareQueue;
        }
    }

認可Serviceにはポリシーリゾルバが必要です。
詳しくは :doc:`/policies` をご覧ください。

.. _identity-decorator:

Identity Decorator
==================

デフォルトの ``identity`` (リクエスト) は ``Authorization\IdentityDecorator`` でデコレートされます。
デコレータクラスは、メソッド呼び出し、配列アクセス、プロパティアクセスをデコレートされたIDオブジェクトにプロキシします。
アクセスするのに基礎となる ``getOriginalData()`` を直接使う::

    $originalUser = $user->getOriginalData();

もしあなたのアプリケーションで、 `cakephp/authentication
<https://github.com/cakephp/authentication>`_ プラグインが使われていたら、
``Authorization\Identity`` クラスを使用しています。
このクラスは ``Authorization\IdentityInterface`` に ``Authentication\IdentityInterface``を加えて実装します。
これにより、 ``Authentication`` のコンポーネントとヘルパーを使用して、デコレートされたIDが取得できます。

Userクラスを識別子として使用する
-------------------------------------

``User`` クラスか識別子クラスがある場合、 ``Authorization\IdentityInterface`` を実装して、
``identityDecorator`` ミドルウェアオプションを使用していた場合、デコレータを省略することができます。
最初に ``User`` クラスを変更します::

    namespace App\Model\Entity;
    use Authorization\AuthorizationServiceInterface;
    use Authorization\IdentityInterface;
    use Authorization\Policy\ResultInterface;
    use Cake\ORM\Entity;
    class User extends Entity implements IdentityInterface
    {
        /**
         * Authorization\IdentityInterface method
         */
        public function can($action, $resource): bool
        {
            return $this->authorization->can($this, $action, $resource);
        }
        /**
         * Authorization\IdentityInterface method
         */
        public function canResult($action, $resource): ResultInterface
        {
            return $this->authorization->canResult($this, $action, $resource);
        }
        /**
         * Authorization\IdentityInterface method
         */
        public function applyScope($action, $resource)
        {
            return $this->authorization->applyScope($this, $action, $resource);
        }
        /**
         * Authorization\IdentityInterface method
         */
        public function getOriginalData()
        {
            return $this;
        }
        /**
         * Setter to be used by the middleware.
         */
        public function setAuthorization(AuthorizationServiceInterface $service)
        {
            $this->authorization = $service;
            return $this;
        }
        // Other methods
    }

必要なインターフェースは実装したので、ミドルウェアの設定を更新しましょう::

    // Application::middleware() メソッド内で
    // Authorization
    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'identityDecorator' => function ($auth, $user) {
            return $user->setAuthorization($auth);
        }
    ]));

既存のタイプヒントを変更する必要がなくなり、ユーザーへのアクセスが可能な場所であれば、どこでも認可ポリシーを使い始めることができます。
Authentication(認証)プラグインを使っているなら、両方のインターフェイスを実装します。::

    use Authorization\IdentityInterface as AuthorizationIdentity;
    use Authentication\IdentityInterface as AuthenticationIdentity;
    class User extends Entity implements AuthorizationIdentity, AuthenticationIdentity
    {
        ...
        
        /**
         * Authentication\IdentityInterface method
         *
         * @return string
         */
        public function getIdentifier()
        {
            return $this->id;
        }
        ...
    }

認可を確実に適用する
---------------------------------

デフォルトでは、 ``AuthorizationMiddleware`` は ``identity`` を含む各リクエストに対して、認可のチェックと回避を行います。
認可が確認できなかった場合 ``AuthorizationRequiredException`` を投げます。
この例外はミドルウェア/コントローラーが動作した **後に** 発生するため、不正アクセスの防止に使えません。
しかし、開発やテストの時は補助として使うことができます。
この動作は、オプションで無効にすることができます::

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'requireAuthorizationCheck' => false
    ]));

不正なリクエストへの対処
------------------------------

デフォルトでは、アプリケーションがスローする認証例外は、ミドルウェアによって再スローされます。
不正なリクエストへの対処を設定し、ユーザーをログインページにリダイレクトさせるなど、
カスタムアクションを実行することができます。:

* ``Exception`` - このハンドラーは例外を再スローします。これはミドルウェアのデフォルトの動作です。
* ``Redirect`` - このハンドラーは、指定されたURLにリクエストをリダイレクトします。
* ``CakeRedirect`` - CakePHPルーターをサポートするハンドラーをリダイレクトします。
  両方のリダイレクトハンドラーは同じ構成オプションを共有します:
* ``url`` - リダイレクトするURL (``CakeRedirect`` はCakePHPルーター構文をサポートします。).
* ``exceptions`` - リダイレクトする必要がある例外クラスのリスト。デフォルトでは
  ``MissingIdentityException`` のみがリダイレクトされます。
* ``queryParam`` - アクセスされたリクエストURLは、リダイレクトURLクエリパラメータにアタッチされます。
  (デフォルトは ``redirect``)
* ``statusCode`` - リダイレクトのHTTPステータスコードで、デフォルトは ``302``
  です。

例::

    use Authorization\Exception\MissingIdentityException;

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'unauthorizedHandler' => [
            'className' => 'Authorization.Redirect',
            'url' => '/pages/unauthorized',
            'queryParam' => 'redirectUrl',
            'exceptions' => [
                MissingIdentityException::class,
                OtherException::class,
            ],
        ],
    ]));

すべてのハンドラは、パラメータとして与えられたスローされた例外オブジェクトを取得します。
この例外はいつも ``Authorization\Exception\Exception`` のインスタンスです。
この例では、 ``Authorization.Redirect`` ハンドラで、どの例外をリスニングするかを指定するオプションが提供されているだけです。
この例では ``Authorization.Redirect`` ハンドラを使用していますが、
他の ``AuthorizationException`` ベースの例外を優雅に処理したい場合は、
``execeptions`` 配列に追加することができます。::

    'exceptions' => [
        MissingIdentityException::class,
        ForbiddenException::class
    ],

`RedirectHandler source <https://github.com/cakephp/authorization/blob/2.next/src/Middleware/UnauthorizedHandler/RedirectHandler.php>`__ を見てください。
設定オプションはハンドラの ``handle()`` メソッドに最後のパラメータとして渡されます。

不正なリクエストでリダイレクトされた後のフラッシュメッセージの追加
----------------------------------------------------------------------

現在、不正なリダイレクトにフラッシュメッセージを追加するストレートな方法はありません。
したがって、フラッシュメッセージ (またはリダイレクト時に発生させたいその他のロジック) を
追加する独自のハンドラを作成する必要があります。

どうやってカスタムUnauthorizedHandlerを作成するか
-------------------------------------------------

#. ``src/Middleware/UnauthorizedHandler/CustomRedirectHandler.php`` ファイルを作成::

    <?php
    declare( strict_types = 1 );
    namespace App\Middleware\UnauthorizedHandler;
    use Authorization\Exception\Exception;
    use Authorization\Middleware\UnauthorizedHandler\RedirectHandler;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    class CustomRedirectHandler extends RedirectHandler {
        public function handle( Exception $exception, ServerRequestInterface $request, array $options = [] ): ResponseInterface {
            $response = parent::handle( $exception, $request, $options );
            $request->getFlash()->error( 'You are not authorized to access that location' );
            return $response;
        }
    }
#.  AuthorizationMiddlewareに、新しいカスタムハンドラを使用するように指示します。::

    // src/Application.php内で

    use Authorization\Exception\MissingIdentityException;
    use Authorization\Exception\ForbiddenException;

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'unauthorizedHandler' => [
            'className' => 'CustomRedirect', // <--- see here
            'url' => '/users/login',
            'queryParam' => 'redirectUrl',
            'exceptions' => [
                MissingIdentityException::class,
                ForbiddenException::class
            ],
            'custom_param' => true,
        ],
    ]));

クラス名として ``Authorization.Redirect`` を使用した場合と同じ設定パラメータがあることがおわかりいただけると思います。
これは、プラグインに存在する RedirectHandler をベースに私たちのハンドラを拡張しているからです。したがって、すべての機能は ``handle()`` 関数内に存在し、私たち自身の機能は ``handle()`` 内に存在します。

カスタムパラメータを追加したい場合は、 ``CustomRedirectHandler`` 内の ``handle()`` 関数で指定した ``$options`` 配列に ``custom_param`` が含まれます。
こちらもご覧ください `CakeRedirectHandler <https://github.com/cakephp/authorization/blob/2.next/src/Middleware/UnauthorizedHandler/CakeRedirectHandler.php>`__ or `RedirectHandler <https://github.com/cakephp/authorization/blob/2.next/src/Middleware/UnauthorizedHandler/RedirectHandler.php>`__ 
