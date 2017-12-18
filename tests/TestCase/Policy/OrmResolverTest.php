<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Test\TestCase\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\OrmResolver;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use TestApp\Model\Entity\Article;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Policy\ArticlePolicy;

class OrmResolverTest extends TestCase
{
    public function testGetPolicyUnknownObject()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $entity = new \stdClass();
        $resolver = new OrmResolver('TestApp');
        $resolver->getPolicy($entity);
    }

    public function testGetPolicyUnknownEntity()
    {
        $this->setExpectedException(MissingPolicyException::class);

        $entity = new Entity();
        $resolver = new OrmResolver('TestApp');
        $resolver->getPolicy($entity);
    }

    public function testGetPolicyDefinedEntity()
    {
        $article = new Article();
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($article);
        $this->assertInstanceOf(ArticlePolicy::class, $policy);
    }

    public function testGetPolicyDefinedPluginEntityAppOveride()
    {
        $article = new Article();
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($article);
        $this->assertInstanceOf(ArticlePolicy::class, $policy);
    }

    public function testGetPolicyDefinedTable()
    {
        $this->markTestIncomplete();
    }

    public function testGetPolicyUnknownTable()
    {
        $this->markTestIncomplete();
    }

    public function testMap()
    {
        $this->markTestIncomplete();
    }

    public function testFallbackPolicy()
    {
        $this->markTestIncomplete();
    }
}
