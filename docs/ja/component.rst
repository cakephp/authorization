AuthorizationComponent
######################

``AuthorizationComponent`` は、コントローラのパーミッションをチェックするための、
いくつかの規約に基づいたヘルパーメソッドを公開しています。
ユーザーの取得や、 ``can`` や ``applyScope`` メソッドの呼び出しが抽象化されています。
AuthorizationComponent を使用するには、ミドルウェアを使用する必要があるので、ミドルウェアが適用されていることを確認してください。コンポーネントを使用するには、まず、次のようにコンポーネントをロードします。::

    // In your AppController
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Authorization.Authorization');
    }

自動認可の確認
==============================

``AuthorizationComponent`` は、コントローラのデフォルトのモデルクラスと現在のアクション名に基づいて、自動的に認可を適用するように設定することができます。
次の例では、 ``index`` と ``add`` のアクションが許可されます。::

    $this->Authorization->authorizeModel('index', 'add');

また、認証をスキップするアクションを設定することも可能です。
これにより、すべてのユーザーがアクセスできる、**public** なアクションが作成されます。デフォルトでは、すべてのアクションは認証が必要で、認証チェックが有効な場合は ``AuthorizationRequiredException`` がスローされます。
Authorizationは個々のアクションをスキップできます。::

    $this->loadComponent('Authorization.Authorization', [
        'skipAuthorization' => [
            'login',
        ]
    ]);

Authorization を確認する
======================

コントローラのアクションやコールバックメソッドで、 認証をチェックするにはコンポーネント::

    // ArticlesControllerの中
    public function edit($id)
    {
        $article = $this->Articles->get($id);
        $this->Authorization->authorize($article);
        // 残りの編集についての処理
    }

上では、現在のユーザーに対して記事が認証されていることがわかります。
チェックするアクションを指定していないので、リクエストの ``action`` が使われます。
第2パラメータでポリシーアクションを指定することができます。::

    // 現在のコントローラアクションに一致しないポリシーメソッドを使用します。
    $this->Authorization->authorize($article, 'update');

``authorize()`` は、拒否されると ``Authorization\Exception\ForbiddenException`` を投げます。 もし、Bool値を取得したいなら ``can()`` を使用してください ::

    if ($this->Authorization->can($article, 'update')) {
        // 記事に関する処理
    }

匿名のユーザー
===============

アプリケーション内の一部のリソースは、ログインしていないユーザーもアクセスできる場合があります。
未認証のユーザーがリソースにアクセスできるかどうかは、ポリシーの領域である。
このコンポーネントを通して、匿名ユーザーの認可を確認することができます。
``can()`` と ``authorize()`` の両方が匿名ユーザーをサポートします。ユーザーがログインしていない場合、ポリシーは 'user' パラメータに ``null`` を期待することができます。

ポリシースコープを適用する
======================

また、ポリシースコープを適用するには、コンポーネント::

$query = $this->Authorization->applyScope($this->Articles->find());

現在のアクションにログインしているユーザがいない場合、 ``MissingIdentityException`` が発生します。

アクションを異なる認証方式にマッピングしたい場合は、 ``actionMap`` オプションを使用します::

   // In your controller initialize() method:
   $this->Authorization->mapActions([
       'index' => 'list',
       'delete' => 'remove',
       'add' => 'insert',
   ]);

   // or map actions individually.
   $this->Authorization
       ->mapAction('index','list')
       ->mapAction('delete', 'remove')
       ->mapAction('add', 'insert');

例::

    //ArticlesController.php

    public function index()
    {
        $query = $this->Articles->find();

        // これは `index` コントローラアクションで呼び出される際に `list` スコープを適用します。
        $this->Authorization->applyScope($query); 
        ...
    }

    public function delete($id)
    {
        $article = $this->Articles->get($id);

        // これは、 `delete` コントローラアクションで呼び出される `remove` エンティティアクションに対して認可を行うものです。
        $this->Authorization->authorize($article); 
        ...
    }

    public function add()
    {
        // これは `add` コントローラアクションで呼び出される `insert` モデルアクションに対して認可を行います。
        $this->Authorization->authorizeModel(); 
        ...
    }

 認可をスキップする
======================

アクションの内部で認証を省略することもできます。::

    //ArticlesController.php

    public function view($id)
    {
        $this->Authorization->skipAuthorization();
        ...
    }
