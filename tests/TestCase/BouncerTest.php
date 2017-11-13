<?php
namespace Cake\Authorization\Test;

use Authorization\Bouncer;
use Cake\TestSuite\TestCase;
use TestApp\Authorization\Policy\Model\Entity\Article as ArticlePolicy;
use TestApp\Authorization\Model\Entity\Article;

/**
 * @property \Cake\Authorization\Bouncer $Gate
 */
class BouncerTest extends TestCase
{

    protected function getIdentityResolver() {
        return function() {
            return [
                'id' => 1,
                'role' => 'admin'
            ];
        };
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function testAllows()
    {
        $resolver = function() {
            return [
                'id' => 1,
                'role' => 'user'
            ];
        };

        $gate = new Bouncer($resolver, [
            Article::class => ArticlePolicy::class
        ]);

        $article = new Article([]);
        $this->assertFalse($gate->allows('add', [$article]));

        $resolver = function() {
            return [
                'id' => 1,
                'role' => 'admin'
            ];
        };

        $gate->setIdentityResolver($resolver);
        $this->assertTrue($gate->allows('add', [$article]));
    }
}
