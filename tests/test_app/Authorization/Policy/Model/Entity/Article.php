<?php
namespace TestApp\Authorization\Policy\Model\Entity;

use TestApp\Authorization\Model\Entity\Article as ArticleEntity;

class Article
{

    /**
     * Create articles if you're an admin or author
     *
     * @param array $user
     * @return bool
     */
    public function canAdd($user)
    {
        return in_array($user['role'], ['admin', 'author']);
    }

    public function canEdit($user, ArticleEntity $article)
    {
        if (in_array($user['role'], ['admin', 'author'])) {
            return true;
        }

        return $article->get('user_id') === $user['id'];
    }

    /**
     * Delete only own articles or any if you're an admin
     *
     * @param array $user
     * @param Article $article
     * @return bool
     */
    public function canDelete($user, ArticleEntity $article)
    {
        if ($user['role'] === 'admin') {
            return true;
        }

        return $user['id'] === $article->get('user_id');
    }

}
