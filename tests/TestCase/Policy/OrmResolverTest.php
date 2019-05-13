<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link https://cakephp.org CakePHP(tm) Project
 * @since 1.0.0
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Test\TestCase\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\OrmResolver;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use OverridePlugin\Policy\TagPolicy as OverrideTagPolicy;
use TestApp\Model\Entity\Article;
use TestApp\Policy\ArticlePolicy;
use TestApp\Policy\ArticlesTablePolicy;
use TestApp\Policy\TestPlugin\BookmarkPolicy;
use TestPlugin\Model\Entity\Bookmark;
use TestPlugin\Model\Entity\Tag;
use TestPlugin\Policy\TagPolicy;

class OrmResolverTest extends TestCase
{
    public $fixtures = ['core.Articles'];

    public function testGetPolicyUnknownObject()
    {
        $this->expectException(MissingPolicyException::class);

        $entity = new \stdClass();
        $resolver = new OrmResolver('TestApp');
        $resolver->getPolicy($entity);
    }

    public function testGetPolicyUnknownEntity()
    {
        $this->expectException(MissingPolicyException::class);

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
        $bookmark = new Bookmark();
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($bookmark);
        $this->assertInstanceOf(BookmarkPolicy::class, $policy);
        $this->assertStringContainsString('TestApp\Policy\TestPlugin', BookmarkPolicy::class, 'class has moved');
    }

    public function testGetPolicyDefinedPluginEntityPluginOveride()
    {
        $bookmark = new Tag();
        $resolver = new OrmResolver('TestApp', [
            'TestPlugin' => 'OverridePlugin',
        ]);
        $policy = $resolver->getPolicy($bookmark);
        $this->assertInstanceOf(OverrideTagPolicy::class, $policy);
        $this->assertStringContainsString('OverridePlugin\Policy', OverrideTagPolicy::class, 'class has moved');
        $this->assertStringNotContainsString('TestApp', OverrideTagPolicy::class, 'class has moved');
        $this->assertStringNotContainsString('TestPlugin', OverrideTagPolicy::class, 'class has moved');
    }

    public function testGetPolicyDefinedPluginEntity()
    {
        $bookmark = new Tag();
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($bookmark);
        $this->assertInstanceOf(TagPolicy::class, $policy);
        $this->assertStringContainsString('TestPlugin\Policy', TagPolicy::class, 'class has moved');
        $this->assertStringNotContainsString('TestApp', TagPolicy::class, 'class has moved');
    }

    public function testGetPolicyDefinedTable()
    {
        $articles = TableRegistry::get('Articles');
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($articles);
        $this->assertInstanceOf(ArticlesTablePolicy::class, $policy);
    }

    public function testGetPolicyQueryForDefinedTable()
    {
        $articles = TableRegistry::get('Articles');
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($articles->find());
        $this->assertInstanceOf(ArticlesTablePolicy::class, $policy);
    }

    public function testGetPolicyUnknownTable()
    {
        $this->expectException(MissingPolicyException::class);

        $articles = $this->createMock('Cake\Datasource\RepositoryInterface');
        $resolver = new OrmResolver('TestApp');
        $policy = $resolver->getPolicy($articles);
    }
}
