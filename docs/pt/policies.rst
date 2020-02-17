Políticas
#########

Políticas são classes que resolvem permissões para um determinado objeto. 
Você pode criar políticas para qualquer classe no seu aplicativo à qual 
deseja aplicar verificações de permissões.

Criando Diretivas
=================

Você pode criar políticas no diretório ``src/Policy``. As classes de política não têm 
uma classe base ou interface comum que se espera que sejam implementadas. As classes 
de aplicativos são então 'resolvidas' para uma classe de política correspondente. 
Consulte a seção :doc:`resolvedores de políticas` para saber como as políticas podem ser resolvidas.

Geralmente, você deseja colocar suas políticas em **src/Policy** e usar o sufixo da 
classe ``Policy``. Por enquanto, criaremos uma classe de política para a entidade 
`Article` em nosso aplicativo. Em **src/Policy/ArticlePolicy.php**, coloque o seguinte conteúdo::

    <?php
    namespace App\Policy;

    use App\Model\Entity\Article;
    use Authorization\IdentityInterface;

    class ArticlePolicy
    {
    }

Além das entidades, os objetos e consultas da tabela podem ter políticas resolvidas. 
Os objetos de consulta terão seu método ``repository()`` chamado e uma classe de 
política será gerada com base no nome da tabela. Uma classe de tabela de 
``App\Model\Table\ArticlesTable`` será mapeada para ``App\Policy\ArticlesTablePolicy``.

Escrevendo Método de Políticas
==============================

A classe de política que acabamos de criar não faz muito agora. Vamos definir um 
método que permita verificar se um usuário pode atualizar um artigo::

    public function canUpdate(IdentityInterface $user, Article $article)
    {
        return $user->id == $article->user_id;
    }

Os métodos de política devem retornar os objetos ``true`` ou ``Result`` para indicar sucesso. 
Todos os outros valores serão interpretados como falha.

Os métodos de política receberão ``null`` para o parâmetro ``$user`` ao manipular usuários 
não autorizados. Se você quiser falhar automaticamente nos métodos de política para usuários 
anônimos, poderá usar o typehint ``IdentityInterface``.

.. _policy-result-objects:

Objetos de rResultado da Diretiva
=================================

Além dos booleanos, os métodos de política podem retornar um objeto ``Result``. 
Os objetos ``Result`` permitem fornecer mais contexto sobre o motivo da 
aprovação/falha da política::

   use Authorization\Policy\Result;

   public function canUpdate(IdentityInterface $user, Article $article)
   {
       if ($user->id == $article->user_id) {
           return new Result(true);
       }
       // Os resultados permitem definir um 'motivo' para a falha.
       return new Result(false, 'not-owner');
   }

Qualquer valor de retorno que não seja ``true`` ou um objeto 
``ResultInterface`` será considerado uma falha.

Escopos de Política
-------------------

Além de as políticas poderem definir verificações de aprovação/reprovação, 
elas também podem definir 'escopos'. Os métodos de escopo permitem modificar 
outro objeto aplicando condições de autorização. Um caso de uso perfeito para 
isso é restringir uma exibição de lista ao usuário atual::

    namespace App\Policy;

    class ArticlesPolicy
    {
        public function scopeIndex($user, $query)
        {
            return $query->where(['Articles.user_id' => $user->getIdentifier()]);
        }
    }

Pré-condições da Política
-------------------------

Em algumas políticas, convém aplicar verificações comuns em todas as operações 
em uma política. Isso é útil quando você precisa negar todas as ações para o 
recurso fornecido. Para usar pré-condições, você precisa implementar o 
``BeforePolicyInterface`` em sua política::

    namespace App\Policy;

    use Authorization\Policy\BeforePolicyInterface;

    class ArticlesPolicy implements BeforePolicyInterface
    {
        public function before($user, $resource, $action)
        {
            if ($user->getOriginalData()->is_admin) {
                return true;
            }
            // falha total
        }
    }

Os hooks before devem retornar um dos três valores:

- ``true`` O usuário pode prosseguir com a ação.
- ``false`` O usuário não tem permissão para continuar com a ação.
- ``null`` O gancho anterior não tomou uma decisão e o método de autorização será chamado.
