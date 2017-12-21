<?php
namespace TestPlugin\Policy;

use Authorization\IdentityInterface;

/**
 * User policy
 */
class UserPolicy
{
    /**
     * Check if $user can create User
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canCreate(IdentityInterface $user, $resource)
    {
    }

    /**
     * Check if $user can update User
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canUpdate(IdentityInterface $user, $resource)
    {
    }

    /**
     * Check if $user can delete User
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canDelete(IdentityInterface $user, $resource)
    {
    }

    /**
     * Check if $user can view User
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestPlugin\Model\Entity\User $resource
     * @return bool
     */
    public function canView(IdentityInterface $user, $resource)
    {
    }
}
