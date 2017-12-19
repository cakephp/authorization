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
namespace Authorization;

/**
 * Interface for Authorization service
 */
interface AuthorizationServiceInterface
{
    /**
     * Check whether the provided user can perform an action on a resource.
     *
     * @param \Authorization\IdentityInterface $user The user to check permissions for.
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @return bool
     */
    public function can(IdentityInterface $user, $action, $resource);
}
