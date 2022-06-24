Middleware Authorization
########################

Le plugin Authorization s'intègre dans votre application en tant que middleware.
Le ``AuthorizationMiddleware`` assume les responsablités suivantes:

* Décorer l'\ 'identity' de la requête avec un décorateur qui ajoute les méthodes
  ``can``, ``canResult``, et ``applyScope`` si nécessaire.
* S'assurer que l'autorisation a été vérifiée ou contournée dans la requête.

Pour utiliser le middleware, implémentez
``AuthorizationServiceProviderInterface`` dans votre classe d'application. Puis
passez votre instance d'application au middleware et ajoutez le middleware à la
middleware queue.

Voici un exemple basique::

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
            // autres middlewares
            $middlewareQueue->add(new AuthorizationMiddleware($this));

            return $middlewareQueue;
        }
    }

Le service d'autorisation a besoin d'un résolveur de policy. Pour savoir quels
sont les résolveurs disponibles et comment les utiliser, consultez la
documentation des :doc:`/policies`.

.. _identity-decorator:

Décorateur d'Identity
=====================

Par défaut, l'\ ``identity`` dans une requête sera décorée (envelopée) par
``Authorization\IdentityDecorator``. La classe du décorateur intercepte les
appels aux méthodes, les accès à la manière des tableaux et les accès aux
propriétés vers l'objet décoré. Utilisez ``getOriginalData()`` pour accéder
directement à l'objet sous-jacent::

    $originalUser = $user->getOriginalData();

Si votre application utilise le plugin `cakephp/authentication
<https://github.com/cakephp/authentication>`_ alors c'est la classe
``Authorization\Identity`` qui sera utilisée. Cette classe implémente
``Authentication\IdentityInterface`` en plus de
``Authorization\IdentityInterface``. Cela vous permet d'utiliser le component et
le helper des bibliothèques de ``Authentication`` pour obtenir l'identity
décorée.

Utiliser votre class User en tant qu'Identity
---------------------------------------------

Si vous avez déjà une classe ``User`` ou une classe d'identité, vous pouvez vous
passer du décorateur en implémentant ``Authorization\IdentityInterface`` et en
utilisant l'option ``identityDecorator`` du middleware. Pour commencer, mettons
à jour notre classe ``User``::

    namespace App\Model\Entity;

    use Authorization\AuthorizationServiceInterface;
    use Authorization\IdentityInterface;
    use Authorization\Policy\ResultInterface;
    use Cake\ORM\Entity;

    class User extends Entity implements IdentityInterface
    {
        /**
         * Méthode Authorization\IdentityInterface
         */
        public function can($action, $resource): bool
        {
            return $this->authorization->can($this, $action, $resource);
        }

        /**
         * Méthode Authorization\IdentityInterface
         */
        public function canResult($action, $resource): ResultInterface
        {
            return $this->authorization->canResult($this, $action, $resource);
        }

        /**
         * Méthode Authorization\IdentityInterface
         */
        public function applyScope($action, $resource)
        {
            return $this->authorization->applyScope($this, $action, $resource);
        }

        /**
         * Méthode Authorization\IdentityInterface
         */
        public function getOriginalData()
        {
            return $this;
        }

        /**
         * Setter utilisé par le middleware.
         */
        public function setAuthorization(AuthorizationServiceInterface $service)
        {
            $this->authorization = $service;

            return $this;
        }

        // Autres méthodes
    }

Maintenant que votre user implémente l'interface nécessaire, mettons à jour la
configuration de notre middleware::

    // Dans votre méthode Application::middleware()

    // Authorization
    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'identityDecorator' => function ($auth, $user) {
            return $user->setAuthorization($auth);
        }
    ]));

Vous n'avez plus à changer les typehints, et vous pouvez commencer à utiliser
les policies d'autorisation partout où vous avez accès à votre user.

Si vous utilisez aussi le plugin Authentication, assurez-vous d'implémenter les
deux interfaces.::

    use Authorization\IdentityInterface as AuthorizationIdentity;
    use Authentication\IdentityInterface as AuthenticationIdentity;

    class User extends Entity implements AuthorizationIdentity, AuthenticationIdentity
    {
        ...
        
        /**
         * Méthode Authentication\IdentityInterface
         *
         * @return string
         */
        public function getIdentifier()
        {
            return $this->id;
        }
        
        ...
    }

S'assurer que Authorization est Appliqué
----------------------------------------

Par défaut, le ``AuthorizationMiddleware`` s'assurera que chaque requête
contenant une ``identity`` a aussi passé ou contourné l'autorisation d'accès. Si
l'autorisation d'accès n'est pas vérifiée, il soulèvera une
``AuthorizationRequiredException``.
Cette exception est soulevée **après** la fin des actions de votre
middleware/controller, donc vous ne pouvez pas vous y fier pour prévenir des
accès non autorisés. Toutefois cela peut être une aide utile pendant le
développement et les tests. Vous pouvez désactiver ce comportement grâce à une
option::

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'requireAuthorizationCheck' => false
    ]));

Gérer les Requêtes Non Autorisées
---------------------------------

Par défaut, le middleware fait suivre les exceptions d'autorisation lancées par
l'application. Vous pouvez configurer des gestionnaires pour les requêtes non
autorisées et exécuter une action personnalisée, par exemple rediriger
l'utilisateur vers la page de connexion.

Les gestionnaires intégrés sont:

* ``Exception`` - ce gestionnaire fera suivre l'exception, c'est le comportement
  par défaut du middleware.
* ``Redirect`` - ce gestionnaire redirigera la requête vers l'URL indiquée.
* ``CakeRedirect`` - gestionnaire de redirection supportant le Router CakePHP.

Les deux gestionnaires de redirection partagent les mêmes options de
configuration:

* ``url`` - URL vers laquelle rediriger (``CakeRedirect`` supporte la syntaxe du
  Router CakePHP).
* ``exceptions`` - une liste de classes d'exceptions à rediriger. Par défaut
  seule ``MissingIdentityException`` est redirigée.
* ``queryParam`` - l'URL à laquelle la requête a tenté d'accéder sera attachée
  à un paramètre query de l'URL de redirection (par défaut ``redirect``).
* ``statusCode`` - le code de statut HTTP d'une redirection, par défaut ``302``.

Par exemple:: 

    use Authorization\Exception\MissingIdentityException;

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'unauthorizedHandler' => [
            'className' => 'Authorization.Redirect',
            'url' => '/pages/accesinterdit',
            'queryParam' => 'redirectUrl',
            'exceptions' => [
                MissingIdentityException::class,
                OtherException::class,
            ],
        ],
    ]));

Tous les gestionnaires prennent comme paramètre l'exception qui a été soulevée.
Cette exception sera toujours une instance de
``Authorization\Exception\Exception``.
Dans cet exemple, le gestionnaire ``Authorization.Redirect`` vous permet
simplement de préciser quelles exceptions vous souhaitez écouter.

Ainsi, dans cet exemple où nous utilisons le gestionnaire
``Authorization.Redirect``, nous pouvons ajouter au tableau ``exceptions``
d'autres exceptions basées sur ``Authorization\Exception\Exception`` si nous
voulons qu'elles soient traitées convenablement::
 
    'exceptions' => [
        MissingIdentityException::class,
        ForbiddenException::class
    ],

Consultez la `source de RedirectHandler <https://github.com/cakephp/authorization/blob/2.next/src/Middleware/UnauthorizedHandler/RedirectHandler.php>`__

Les options de configuration sont passées à la méthode ``handle()`` du
gestionnaire comme dernier paramètre.

Ajouter un message flash après avoir été redirigé par une requête non autorisée
-------------------------------------------------------------------------------

Pour l'instant, il n'y a pas de moyen idéal pour ajouter un message flash lors
d'une redirection suite à une requête non autorisée.

Par conséquent vous devez créer votre propre Handler, qui ajoutera le message
flash (ou toute autre logique que vous voudriez exécuter lors d'une
redirection).

Comment créer un UnauthorizedHandler personnalisé
-------------------------------------------------

#. Créez le fichier ``src/Middleware/UnauthorizedHandler/CustomRedirectHandler.php``::

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
            $request->getFlash()->error( "Vous n'êtes pas autorisé à accéder à cette ressource" );
            return $response;
        }
    }

#. Dites au AuthorizationMiddleware d'utiliser votre nouveau Handler
   personnalisé::

    // dans votre src/Application.php

    use Authorization\Exception\MissingIdentityException;
    use Authorization\Exception\ForbiddenException;
    
    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'unauthorizedHandler' => [
            'className' => 'CustomRedirect', // <--- c'est ici !
            'url' => '/users/login',
            'queryParam' => 'redirectUrl',
            'exceptions' => [
                MissingIdentityException::class,
                ForbiddenException::class
            ],
            'custom_param' => true,
        ],
    ]));

Comme vous le voyez, vous pouvez conserver la même configuration que si vous
utilisiez le nom de classe ``Authorization.Redirect``.

Cela est dû au fait que notre Handler étend le RedirectHandler présent dans le
plugin. Ainsi, il contient toutes les fonctionnalités d'origin + notre propre
logique dans la fonction  ``handle()``.

Le ``custom_param`` apparaît dans le tableau ``$options`` qui vous sera donné
dans la fonction ``handle()`` à l'intérieur de votre ``CustomRedirectHandler``
si vous souhaitez ajouter quelques paramètres de configuration supplémentaires à
vos fonctionnalités personnalisées.

Vous pouvez consulter les classes `CakeRedirectHandler <https://github.com/cakephp/authorization/blob/2.next/src/Middleware/UnauthorizedHandler/CakeRedirectHandler.php>`__ ou `RedirectHandler <https://github.com/cakephp/authorization/blob/2.next/src/Middleware/UnauthorizedHandler/RedirectHandler.php>`__ 
pour voir à quoi un Handler pourrait/devrait ressembler.
