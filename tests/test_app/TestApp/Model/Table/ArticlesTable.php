<?php
namespace TestApp\Model\Table;

use Authorization\AuthorizationAwareInterface;
use Authorization\BouncerTrait;
use Cake\ORM\Query;
use Cake\ORM\Table;

class ArticlesTable extends Table implements AuthorizationAwareInterface
{
    use BouncerTrait;

    public function findFieldsByPermission(Query $query)
    {
        if (!$this->can('selectSpecialFields')) {
            return $query->select([
                'field1',
                'field2',
                'field3'
            ]);
        }

        return $query;
    }
}
