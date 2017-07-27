<?php
namespace Cake\Authorization\Test;

use Authorization\PolicyLocator;
use Cake\TestSuite\TestCase;
use Cake\Authorization\Bouncer;
use TestApp\Authorization\Controller\ArticlesController;
use TestApp\Authorization\Policy\Articles;
use TestApp\Authorization\Model\Entity\Article;

/**
 * @property \Cake\Authorization\Gate $Gate
 */
class PolicyLocatorTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function testAllows()
    {
        $controller = new ArticlesController();

        $locator = new PolicyLocator();
        $result = $locator->locate($controller);
        //dd($result);
    }
}
