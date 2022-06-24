Policies
########

Policiesは与えられたオブジェクトの権限を解決するクラスです。
アプリケーション内の任意のクラスに対して、権限の確認を適用するPoliciesを作成することができます。

Policies を作成する
===================

``src/Policy`` ディレクトリにPoliciesを作成します。
クラスが用意されていないため、自分で作成する必要があります。
アプリケーションのクラスはポリシークラスによって、'resolved'(解決)されます。
ポリシーの解決については :doc:`policy-resolvers` セクションをご覧ください。

ポリシーは **src/Policy** において、クラスの末尾に ``Policy`` をつけます。
一旦例として、 `Article` エンティティのためのポリシーを作成します。
**src/Policy/ArticlePolicy.php** に以下を追加します。
::

    <?php
    namespace App\Policy;

    use App\Model\Entity\Article;
    use Authorization\IdentityInterface;

    class ArticlePolicy
    {
    }

エンティティだけでなく、テーブルやクエリ自体が解決されることもあります。
Queryオブジェクトの ``repository()`` メソッドが呼び出され、テーブル名に基づいてポリシークラスが生成されます。
``App\Model\Table\ArticlesTable`` なら、 ``App\Policy\ArticlesTablePolicy`` になります。

ORMオブジェクトのポリシーを作成するには ``bake`` コマンドを使用します。(便利ですね):

.. code-block:: bash

    # Create an entity policy
    bin/cake bake policy --type entity Article

    # Create a table policy
    bin/cake bake policy --type table Articles

ポリシーメソッドの書き方
========================

さっき作成したばかりのポリシークラスはまだ何もしてくれません。
ユーザーが記事を更新できるかのチェックをするメソッドを定義してみましょう。::

    public function canUpdate(IdentityInterface $user, Article $article)
    {
        return $user->id == $article->user_id;
    }

ポリシーメソッドは ``true`` か ``Result(真偽の結果)`` を返す必要があります。
それ以外は失敗と解釈します。

認証していない場合、 ``$user`` は ``null`` が代入されます。
匿名ユーザーに対するポリシーメソッドを失敗させたい場合、 ``IdentityInterface`` のType Hintingを使用することができます。.

.. _policy-result-objects:

ポリシーのResultオブジェクト
============================

ポリシーメソッドの返り値はBool以外にも ``Result`` オブジェクトを返すことができます。
``Result`` オブジェクトはポリシーの成功・失敗について、より多くの情報を提供することができます。::

   use Authorization\Policy\Result;

   public function canUpdate(IdentityInterface $user, Article $article)
   {
       if ($user->id == $article->user_id) {
           return new Result(true);
       }
       // 失敗の理由を定義できる。
       return new Result(false, 'not-owner');
   }

戻り値が ``true`` か ``ResultInterface`` 以外は失敗と解釈されます。

ポリシーのスコープ
------------------

ポリシーは認可の可否だけでなく、「スコープ」を定義することもできます。 
スコープメソッドは認可の条件を適用して他のオブジェクトを変更することができます。
リストの取得を現在のユーザーに限定するときに最適です。::

    namespace App\Policy;

    class ArticlesTablePolicy
    {
        public function scopeIndex($user, $query)
        {
            return $query->where(['Articles.user_id' => $user->getIdentifier()]);
        }
    }

ポリシーの前提条件
---------------------

ポリシーによっては、ポリシー内のすべての操作に共通のチェックを適用したい場合があります。
全てのアクションを拒否する必要があるときに便利です。
前提条件として ``BeforePolicyInterface`` をポリシーに追加する必要があります。::

    namespace App\Policy;

    use Authorization\Policy\BeforePolicyInterface;

    class ArticlesPolicy implements BeforePolicyInterface
    {
        public function before($user, $resource, $action)
        {
            if ($user->getOriginalData()->is_admin) {
                return true;
            }
            // fall through
        }
    }

3つの値を返却するbeforeが必要です。:

- ``true`` 実行を許可します。
- ``false`` 実行を拒否します。
- ``null`` 判断できないので、authorizationメソッドが呼び出されます。
