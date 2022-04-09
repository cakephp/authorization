Middleware d'Autorisation de Requête
####################################

Ce middleware est utile pour autoriser vos requêtes, par exemple chaque
controller et action, en fonction d'un système d'accès basé sur des rôles ou
n'importe quel autre type de processus d'autorisation qui contrôle l'accès à
certaines actions.

Il **doit** être ajouté après Authorization, Authentication et RoutingMiddleware
dans la Middleware Queue !
La logique de gestion de l'autorisation de la requête sera implémentée dans la
policy de la requête. Vous pouvez ajouter toute votre logique à cet endroit ou
simplement passer l'information de la requête vers une implémentation ACL ou
RBAC.

Comment l'utiliser
==================

Créez une policy pour gérer l'objet requête. Le plugin est livré avec une
interface que nous pouvons implémenter. Commencez par créer
**src/Policy/RequestPolicy.php** et ajoutez-y::

    namespace App\Policy;

    use Authorization\Policy\RequestPolicyInterface;
    use Cake\Http\ServerRequest;

    class RequestPolicy implements RequestPolicyInterface
    {
        /**
         * Méthode pour vérifier si on peut accéder à la requête
         *
         * @param \Authorization\IdentityInterface|null $identity Identity
         * @param \Cake\Http\ServerRequest $request Requête du serveur
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

Ensuite, mappez la classe de la requête vers la policy à l'intérieur de
``Application::getAuthorizationService()``, dans **src/Application.php** ::

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

Assurez-vous de charger RequestAuthorizationMiddleware **après**
AuthorizationMiddleware::

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
        // autres middlewares...
        // $middlewareQueue->add(new AuthenticationMiddleware($this));

        // Ajoutez l'autorisation (après authentication si vous utilisez aussi ce plugin).
        $middlewareQueue->add(new AuthorizationMiddleware($this));
        $middlewareQueue->add(new RequestAuthorizationMiddleware());
    }
