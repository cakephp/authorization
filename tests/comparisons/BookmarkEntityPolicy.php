<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\IdentityInterface;
use TestApp\Model\Entity\Bookmark;

/**
 * Bookmark policy
 */
class BookmarkPolicy
{
    /**
     * Check if $user can add/create Bookmark
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Bookmark $bookmark)
    {
    }

    /**
     * Check if $user can edit/update Bookmark
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Bookmark $bookmark)
    {
    }

    /**
     * Check if $user can delete Bookmark
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Bookmark $bookmark)
    {
    }

    /**
     * Check if $user can view Bookmark
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \TestApp\Model\Entity\Bookmark $bookmark
     * @return bool
     */
    public function canView(IdentityInterface $user, Bookmark $bookmark)
    {
    }
}
