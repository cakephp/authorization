<?php
declare(strict_types=1);
namespace TestApp\Policy;

use Cake\Datasource\QueryInterface;
use Phauthentic\Authorization\IdentityInterface;
use Phauthentic\Authorization\Policy\Result;

class ArticlesTablePolicy
{
    public function canIndex(IdentityInterface $identity)
    {
        return new Result($identity['can_index']);
    }

    public function canEdit(IdentityInterface $identity)
    {
        return new Result($identity['can_edit']);
    }

    public function canModify(IdentityInterface $identity)
    {
        return new Result($identity['can_edit']);
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
}
