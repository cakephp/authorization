<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\QueryInterface;

class ArticlesTablePolicy
{
    public function canIndex(IdentityInterface $identity)
    {
        return $identity['can_index'];
    }

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
            'user_id' => $user['id'],
        ]);
    }

    public function scopeModify(IdentityInterface $user, QueryInterface $query)
    {
        return $query->where([
            'identity_id' => $user['id'],
        ]);
    }

    public function scopeOptions(IdentityInterface $user, QueryInterface $query, array $options)
    {
        return $query->where([$options['column'] => $user['id']]);
    }
}
