<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization;

use ArrayAccess;

/**
 * Interface for describing identities that can have authorization checked.
 *
 * This interface is similar to the interface defined in cakephp/authentication
 * and uses ArrayAccess to expose public properties of the wrapped identity
 * implementation.
 */
interface IdentityInterface extends ArrayAccess
{
    /**
     * Check whether the current identity can perform an action.
     *
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @return bool
     */
    public function can($action, $resource);

    /**
     * Get the decorated identity
     *
     * If the decorated identity implements `getOriginalData()`
     * that method should be invoked to expose the original data.
     *
     * @return array|ArrayAccess
     */
    public function getOriginalData();
}
