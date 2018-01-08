<?php
namespace TestApp\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\QueryInterface;

class ArticlesTablePolicy
{
    public function canEdit(IdentityInterface $identity)
    {
        return $identity['can_edit'];
    }

    public function canModify(IdentityInterface $identity)
    {
        return $identity['can_edit'];
    }

    public function scopeEdit(IdentityInterface $user, QueryInterface $query)
    {
        return $query->where([
            'user_id' => $user['id']
        ]);
    }

    public function scopeModify(IdentityInterface $user, QueryInterface $query)
    {
        return $query->where([
            'user_ID' => $user['id']
        ]);
    }
}
