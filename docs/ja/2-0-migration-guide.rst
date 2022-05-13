2.0 Migration Guide
###################

Authorization 2.0 には新しい機能と、少しの破壊的変更が含まれています。

破壊的変更
================

``IdentityInterface`` にタイプヒントが追加されました。
もし ``IdentityInterface`` を実装している場合は、新しい typehints を反映させるためにアプリケーションの実装を更新する必要があります。

タイプヒントを加えて、 ``IdentityInterface`` に ``canResult()`` メソッドが追加されました。 
このメソッドは常に ``ResultInterface`` オブジェクトを返し ``can()`` は常にboolを返します。
1.xバージョンの時は ``can()`` は ``bool`` と ``ResultInterface`` が返却されていました。
このため、 ``can()`` の戻り値を知ることは非常に困難でした。
新しいメソッドと追加の型付けにより、 ``IdentityInterface`` はよりシンプルに、より信頼性の高いものとして使用できるようになりました。