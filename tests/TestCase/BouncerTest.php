<?php
namespace Cake\Authorization\Test;

use Cake\TestSuite\TestCase;
use Cake\Authorization\Bouncer;
use TestApp\Authorization\Policy\Articles;
use TestApp\Model\Entity\Article;

/**
 * @property \Cake\Authorization\Gate $Gate
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
            Article::class => Articles::class
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
