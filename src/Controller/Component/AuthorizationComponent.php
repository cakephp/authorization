<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Controller\Component;

use Authorization\Exception\ForbiddenException;
use Authorization\IdentityInterface;
use Cake\Controller\Component;
use Cake\Http\ServerRequest;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

/**
 * Authorization Component
 *
 * Makes it easier to check authorization in CakePHP controllers.
 * Applies conventions on matching policy methods to controller actions,
 * and raising errors when authorization fails.
 */
class AuthorizationComponent extends Component
{

    /**
     * Constant for all actions config key.
     */
    const ALL = '*';

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'identityAttribute' => 'identity',
        'authorizationEvent' => 'Controller.initialize',
        'authorizeModel' => [
            self::ALL => true,
        ],
        'actionMap' => []
    ];

    /**
     * Check the policy for $resource, raising an exception on error.
     *
     * If $action is left undefined, the current controller action will
     * be used.
     *
     * @param object $resource The resource to check authorization on.
     * @param string|null $action The action to check authorization for.
     * @return void
     * @throws \Authorization\Exception\ForbiddenException when policy check fails.
     */
    public function authorize($resource, $action = null)
    {
        $request = $this->getController()->request;
        if ($action === null) {
            $action = $this->getDefaultAction($request);
        }
        $identity = $this->getIdentity($request);
        if (!$identity->can($action, $resource)) {
            throw new ForbiddenException([$action, get_class($resource)]);
        }
    }

    /**
     * Applies a scope for $resource.
     *
     * If $action is left undefined, the current controller action will
     * be used.
     *
     * @param object $resource The resource to apply a scope to.
     * @param string|null $action The action to apply a scope for.
     * @return object
     */
    public function applyScope($resource, $action = null)
    {
        $request = $this->getController()->request;
        if ($action === null) {
            $action = $this->getDefaultAction($request);
        }
        $identity = $this->getIdentity($request);

        return $identity->applyScope($action, $resource);
    }

    /**
     * Get the identity from a request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return \Authorization\IdentityInterface
     */
    protected function getIdentity(ServerRequestInterface $request)
    {
        $identityAttribute = $this->getConfig('identityAttribute');
        $identity = $request->getAttribute($identityAttribute);
        if (!$identity instanceof IdentityInterface) {
            $type = is_object($identity) ? get_class($identity) : gettype($identity);
            throw new InvalidArgumentException(sprintf(
                'Expected that `%s` would be an instance of %s, but got %s',
                $identityAttribute,
                IdentityInterface::class,
                $type
            ));
        }

        return $identity;
    }

    /**
     * Model authorization handler.
     *
     * @return void
     */
    public function authorizeModel()
    {
        $action = $this->getController()->request->getParam('action');

        if ($this->shouldAuthorizeAction($action, 'authorizeModel')) {
            $this->authorize($this->getController()->loadModel());
        }
    }

    /**
     * Checks whether an action should be authorized according to the config key provided.
     *
     * @param string $action Action name.
     * @param string $configKey Configuration key with actions.
     * @return bool
     */
    protected function shouldAuthorizeAction($action, $configKey)
    {
        $value = $this->getConfig($configKey . '.' . $action);
        if (is_bool($value)) {
            return $value;
        }

        return (bool)$this->getConfig($configKey . '.' . static::ALL);
    }

    /**
     * Returns authorization action name for a controller action resolved from the request.
     *
     * @param \Cake\Http\ServerRequest $request Server request.
     * @return string
     * @throws UnexpectedValueException When invalid action type encountered.
     */
    protected function getDefaultAction(ServerRequest $request)
    {
        $action = $request->getParam('action');
        $name = $this->getConfig('actionMap.' . $action);

        if ($name === null) {
            return $action;
        }
        if (!is_string($name)) {
            $type = is_object($name) ? get_class($name) : gettype($name);
            $message = sprintf('Invalid action type for `%s`. Expected `string` or `null`, got `%s`.', $action, $type);
            throw new UnexpectedValueException($message);
        }

        return $name;
    }

    /**
     * Returns model authorization handler if model authorization is enabled.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            $this->getConfig('authorizationEvent') => 'authorizeModel'
        ];
    }
}
