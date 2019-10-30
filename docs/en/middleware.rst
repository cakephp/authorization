Authorization Middleware
########################

Authorization is applied to your application as a middleware. The
``AuthorizationMiddleware`` handles the following responsibilities:

* Decorating the request 'identity' with a decorator that adds the ``can`` and
  ``applyScope`` if necessary.
* Ensuring that authorization has been checked/bypassed in the request.

To use the middleware implement ``AuthorizationServiceProviderInterface`` in your
application class. Then pass your app instance into the middlware and add the
middleware to the queue.

A very simple example would be::

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

The authorization service requires a policy resolver. See the
:doc:`/policies` documentation on what resolvers are available and how
to use them.

.. _identity-decorator:

Identity Decorator
==================

By default the ``identity`` in the request will be decorated (wrapped) with
``Authorization\IdentityDecorator``. The decorator class proxies most read
operations and method calls to the wrapped identity.

If your application uses the `cakephp/authentication <https://github.com/cakephp/authentication>`_ plugin too
then by default the ``Authorization\Identity`` class is used which also implements
the ``Authentication\IdentityInterface``. This allows you to use the ``Authentication``
lib's component and helper to get the decorated identity.

If you have an existing
``User`` or identity class you can skip the decorator by implementing the
``Authorization\IdentityInterface`` and using the ``identityDecorator``
middleware option. First lets update our ``User`` class::

    namespace App\Model\Entity;

    use Authorization\AuthorizationServiceInterface;
    use Authorization\IdentityInterface;
    use Cake\ORM\Entity;


    class User extends Entity implements IdentityInterface
    {

        /**
         * Authorization\IdentityInterface method
         */
        public function can($action, $resource)
        {
            return $this->authorization->can($this, $action, $resource);
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

Now that our user implements the necessary interface, lets update our middleware
setup::

    // In your Application::middleware() method;

    // Authorization
    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'identityDecorator' => function ($auth, $user) {
            return $user->setAuthorization($auth);
        }
    ]));

You no longer have to change any existing typehints, and can start using
authorization policies anywhere you have access to your user.

Ensuring Authorization is Applied
---------------------------------

By default the ``AuthorizationMiddleware`` will ensure that each request
containing an ``identity`` also has authorization checked/bypassed. If
authorization is not checked an ``AuthorizationRequiredException`` will be raised.
This exception is raised **after** your other middleware/controller actions are
complete, so you cannot rely on it to prevent unauthorized access, however it is
a helpful aid during development/testing. You can disable this behavior via an
option::

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'requireAuthorizationCheck' => false
    ]));

Handling unauthorized requests
------------------------------

By default authorization exceptions thrown by the application are rethrown by the middleware.
You can configure handlers for unauthorized requests and perform custom action, e.g.
redirect the user to the login page.

The built-in handlers are:

* ``Exception`` - this handler will rethrow the exception, this is a default behavior of the middleware.
* ``Redirect`` - this handler will redirect the request to the provided URL.
* ``CakeRedirect`` - redirect handler with support for CakePHP Router.

Both redirect handlers share the same configuration options:

* ``url`` - URL to redirect to (``CakeRedirect`` supports CakePHP Router syntax).
* ``exceptions`` - a list of exception classes that should be redirected. By default only ``MissingIdentityException`` is redirected.
* ``queryParam`` - the accessed request URL will be attached to the redirect URL query parameter (``redirect`` by default).
* ``statusCode`` - HTTP status code of a redirect, ``302`` by default.

For example::

    $middlewareQueue->add(new AuthorizationMiddleware($this, [
        'unauthorizedHandler' => [
            'className' => 'Authorization.Redirect',
            'url' => '/users/login',
            'queryParam' => 'redirectUrl',
            'exceptions' => [
                MissingIdentityException::class,
                OtherException::class,
            ],
        ],
    ]));

You can also add your own handler. Handlers should implement ``Authorization\Middleware\UnauthorizedHandler\HandlerInterface``,
be suffixed with ``Handler`` suffix and reside under your app's or plugin's
``Middleware\UnauthorizedHandler`` namespace.

Configuration options are passed to the handler's ``handle()`` method as the last parameter.

Handlers catch only those exceptions which extend the ``Authorization\Exception\Exception`` class.
