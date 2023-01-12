<?php
declare(strict_types=1);

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

use ArrayAccess;
use Authorization\Policy\ResultInterface;

/**
 * Interface for describing identities that can have authorization checked.
 *
 * This interface is similar to the interface defined in cakephp/authentication
 * and uses ArrayAccess to expose public properties of the wrapped identity
 * implementation.
 *
 * @extends \ArrayAccess<array-key, mixed>
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
    public function can(string $action, mixed $resource): bool;

    /**
     * Check whether the current identity can perform an action.
     *
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @return \Authorization\Policy\ResultInterface
     */
    public function canResult(string $action, mixed $resource): ResultInterface;

    /**
     * Apply authorization scope conditions/restrictions.
     *
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @param mixed $optionalArgs Multiple additional arguments which are passed to the scope
     * @return mixed The modified resource.
     */
    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed;

    /**
     * Get the decorated identity
     *
     * If the decorated identity implements `getOriginalData()`
     * that method should be invoked to expose the original data.
     *
     * @return \ArrayAccess|array
     */
    public function getOriginalData(): ArrayAccess|array;
}
