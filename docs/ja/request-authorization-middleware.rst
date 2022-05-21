リクエスト認証ミドルウェア
################################

このミドルウェアは、例えば各コントローラーやアクションなどのリクエストを、
ロールベースアクセスシステムや、
特定のアクションへのアクセスを制御する他の種類の認可プロセスに対して認可したい場合に便利です。
Authorization や RoutingMiddleware の **後に** 追加する必要があります

リクエスト認可を処理するロジックは、リクエストポリシーに実装される。
そこですべてのロジックを追加することもできるし、リクエストからの情報をACLやRBACの実装に渡すだけでいい。

使用方法
========

リクエストオブジェクトを処理するためのポリシーを作成します。プラグインは実装可能なインターフェイスを同梱しています。
まず、 **src/Policy/RequestPolicy.php** を作成し、以下を追加します。::

    namespace App\Policy;

    use Authorization\Policy\RequestPolicyInterface;
    use Cake\Http\ServerRequest;

    class RequestPolicy implements RequestPolicyInterface
    {
        /**
         * Method to check if the request can be accessed
         *
         * @param \Authorization\IdentityInterface|null $identity Identity
         * @param \Cake\Http\ServerRequest $request Server Request
         * @return bool
         */
        public function canAccess($identity, ServerRequest $request)
        {
            if ($request->getParam('controller') === 'Articles'
                && $request->getParam('action') === 'index'
            ) {
                return true;
            }

            return false;
        }
    }

次に、 **src/Application.php** の ``Application::getAuthorizationService()`` 内で、リクエストクラスをポリシーにマッピングします。::

    use App\Policy\RequestPolicy;
    use Authorization\AuthorizationService;
    use Authorization\AuthorizationServiceInterface;
    use Authorization\AuthorizationServiceProviderInterface;
    use Authorization\Middleware\AuthorizationMiddleware;
    use Authorization\Middleware\RequestAuthorizationMiddleware;
    use Authorization\Policy\MapResolver;
    use Authorization\Policy\OrmResolver;
    use Psr\Http\Message\ResponseInterface;
    use Cake\Http\ServerRequest;


    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface {
        $mapResolver = new MapResolver();
        $mapResolver->map(ServerRequest::class, RequestPolicy::class);
        return new AuthorizationService($mapResolver);
    }

RequestAuthorizationMiddlewareの読み込みが、AuthorizationMiddlewareの **後** であることを確認する。::

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
        // other middleware...
        // $middlewareQueue->add(new AuthenticationMiddleware($this));

        // authorizationを加える (authenticationの後にね).
        $middlewareQueue->add(new AuthorizationMiddleware($this));
        $middlewareQueue->add(new RequestAuthorizationMiddleware());
    }
