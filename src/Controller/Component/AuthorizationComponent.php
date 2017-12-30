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

use Authorization\IdentityInterface;
use Cake\Controller\Component;
use Cake\Network\Exception\ForbiddenException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

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
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'identityAttribute' => 'identity',
        'forbiddenException' => ForbiddenException::class,
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
            $action = $request->getParam('action');
        }
        $identity = $this->getIdentity($request);
        if (!$identity->can($action, $resource)) {
            $class = $this->getConfig('forbiddenException');
            throw new $class();
        }
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
}
