<?php
namespace TestApp\Policy;

use Authorization\IdentityInterface;

/**
 * Bookmark policy
 */
class BookmarkPolicy
{
    /**
     * Check if $user can create Bookmark
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canCreate(IdentityInterface $user, $bookmark)
    {
    }

    /**
     * Check if $user can update Bookmark
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canUpdate(IdentityInterface $user, $bookmark)
    {
    }

    /**
     * Check if $user can delete Bookmark
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canDelete(IdentityInterface $user, $bookmark)
    {
    }

    /**
     * Check if $user can view Bookmark
     *
     * @param Authorization\IdentityInterface $user The user.
     * @param TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canView(IdentityInterface $user, $bookmark)
    {
    }
}
