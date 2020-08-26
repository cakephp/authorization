<?php
declare(strict_types=1);

namespace TestPlugin\Policy;

use Authorization\IdentityInterface;
use TestPlugin\Model\Entity\User;

/**
 * User policy
 */
class UserPolicy
{
    /**
     * Check if $user can add/create User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canAdd(IdentityInterface $user, User $resource)
    {
    }

    /**
     * Check if $user can edit/update User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canEdit(IdentityInterface $user, User $resource)
    {
    }

    /**
     * Check if $user can delete User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canDelete(IdentityInterface $user, User $resource)
    {
    }

    /**
     * Check if $user can view User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canView(IdentityInterface $user, User $resource)
    {
    }
}
