ポリシーリゾルバー
################

リソースオブジェクトをそれぞれのポリシークラスにマッピングすることは、ポリシーリゾルバによって処理される動作である。
いくつかのリゾルバを用意していますが、 ``AuthorizationPolicyResolverInterface`` を実装することで、独自のリゾルバを作成することができます。
内部のリゾルバ:

* ``MapResolver`` では、リソース名をそのポリシークラス名、またはオブジェクトやcallableにマッピングすることができます。
* ``OrmResolver`` は、一般的なORMオブジェクトに対して、規約に基づいたポリシー解決を適用します。
* ``ResolverCollection`` では、複数のリゾルバを集約して、順番に検索することができます。

MapResolver を使う
=================

``MapResolver`` では、リソースクラス名をポリシークラス名、ポリシーオブジェクト、
またはファクトリーコーラブルにマッピングすることができます。::

    use Authorization\Policy\MapResolver;

    $mapResolver = new MapResolver();

    // ポリシークラス名とリソースクラスの対応付け
    $mapResolver->map(Article::class, ArticlePolicy::class);

    // policyインスタンスとリソースクラスの対応付け
    $mapResolver->map(Article::class, new ArticlePolicy());

    // ファクトリークラスとリソースクラスの対応付け
    $mapResolver->map(Article::class, function ($resource, $mapResolver) {
        // Return a policy object.
    });

OrmResolverを使う
=================

``OrmResolver`` は、CakePHP の ORM 用の規約ベースのポリシーリゾルバである。OrmResolver は以下の規約を適用する。:

#. ポリシーは ``App\Policy`` に保存されます。
#. ポリシークラスは末尾が ``Policy`` で終わります。

OrmResolverは、以下のオブジェクトタイプのポリシーを解決することができます。:

* Entities - エンティティクラス名の使用
* Tables - テーブルクラスの使用
* Queries - クエリの ``repository()`` の戻り値を使用して、クラス名を取得する。

すべての場合において、以下のルールが適用されます。:

#. リソースクラス名は、ポリシークラス名を生成するために使用されます。例えば、 ``AppModelEntity Filter`` は ``AppPolicy Filter`` にマップされます。
#. プラグインリソースは、まずアプリケーションポリシーを確認する。 例えば ``App\Policy\Bookmarks\BookmarkPolicy`` は ``Bookmarks\Model\Entity\Bookmark``に向けて。
#. アプリケーションオーバーライドポリシーが見つからない場合、プラグインポリシーがチェックされます。例えば ``Bookmarks\Policy\BookmarkPolicy``

テーブルオブジェクトの場合、クラス名の変換で ``AppModelTableTable`` が ``AppPolicyArticlesTablePolicy`` にマッピングされることになります。
クエリーオブジェクトは ``repository()`` メソッドを呼び出され、その結果得られるテーブルクラスに基づいてポリシーが生成されます。

OrmResolver は、そのコンストラクタでカスタマイズをサポートします。::

    use Authorization\Policy\OrmResolver;

    // カスタムアプリケーションの名前空間を使用する場合の変更。
    $appNamespace = 'App';

    // Map policies in one namespace to another.
    // Here we have mapped policies for classes in the ``Blog`` namespace to be 
    // found in the ``Cms`` namespace.
    $overrides = [
        'Blog' => 'Cms',
    ];
    $resolver = new OrmResolver($appNamespace, $overrides)

ResolverCollectionを使う
========================

``ResolverCollection``  では、複数のリゾルバをまとめることができます。::

    use Authorization\Policy\ResolverCollection;
    use Authorization\Policy\MapResolver;
    use Authorization\Policy\OrmResolver;

    $ormResolver = new OrmResolver();
    $mapResolver = new MapResolver();

    // マップリゾルバをチェックし、リソースが明示的にマップされていない場合は、オームリゾルバにフォールバックする。
    $resolver = new ResolverCollection([$mapResolver, $ormResolver]);

Resolver を作成する
===================

独自のリゾルバを実装するには、 ``AuthorizationPolicyResolverInterface`` を実装し、 
``getPolicy($resource)`` メソッドを定義する必要があります。
