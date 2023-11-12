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
 * @since         2.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Policy;

use Authorization\IdentityInterface;

/**
 * This interface should be implemented if a policy class needs to perform a
 * pre-authorization check before the scope is applied to the resource.
 */
interface BeforeScopeInterface
{
    /**
     * Defines a pre-scope check.
     *
     * If a non-null value is returned, the scope application will be skipped and the un-scoped resource
     * will be returned. In case of `null`, the scope will be applied.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity object.
     * @param mixed $resource The resource being operated on.
     * @param string $action The action/operation being performed.
     * @return mixed
     */
    public function beforeScope(?IdentityInterface $identity, mixed $resource, string $action): mixed;
}
