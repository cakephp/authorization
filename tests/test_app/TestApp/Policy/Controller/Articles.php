<?php
namespace TestApp\Authorization\Policy\Controller;

class Articles
{

    /**
     * Create articles if you're an admin or author
     *
     * @param array $user
     * @return bool
     */
    public function add($user)
    {
        return in_array($user['role'], ['admin', 'author']);
    }

    public function edit($user, Article $article)
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
    public function delete($user, Article $article)
    {
        if ($user['role'] === 'admin') {
            return true;
        }

        return $user['id'] === $article->get('user_id');
    }

}
