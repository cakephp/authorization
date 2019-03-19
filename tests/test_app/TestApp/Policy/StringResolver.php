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
namespace TestApp\Policy;

use Cake\Core\App;
use Phauthentic\Authorization\Policy\Exception\MissingPolicyException;
use Phauthentic\Authorization\Policy\ResolverInterface;

/**
 * Very simple policy resolver that accepts string policy names.
 */
class StringResolver implements ResolverInterface
{
    /**
     * Get a policy for a string.
     *
     * @param string $resource The resource.
     * @return object
     * @throws \Authorization\Policy\Exception\MissingPolicyException When a policy for the
     *   resource has not been defined or cannot be resolved.
     */
    public function getPolicy($resource)
    {
        $policyClass = App::className('TestApp.' . $resource, 'Policy', 'Policy');

        if ($policyClass === false) {
            throw new MissingPolicyException([$resource]);
        }

        return new $policyClass();
    }
}
