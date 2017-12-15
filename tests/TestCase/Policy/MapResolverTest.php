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
use Authorization\Policy\MapResolver;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use TestApp\Authorization\Model\Entity\Article;
use TestApp\Authorization\Policy\Model\Entity\Article as ArticlePolicy;

class MapResolverTest extends TestCase
{
    public function testGetPolicy()
    {
        $resolver = new MapResolver();

        $resolver->map(Article::class, ArticlePolicy::class);

        $policy = $resolver->getPolicy(new Article());
        $this->assertInstanceOf(ArticlePolicy::class, $policy);
    }

    public function testMapMissing()
    {
        $resolver = new MapResolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Policy class `Foo` does not exist.');

        $resolver->map(Article::class, 'Foo');
    }

    public function testGetPolicyPrimitive()
    {
        $resolver = new MapResolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource must be an object, `string` given.');

        $resolver->getPolicy('Foo');
    }

    public function testGetPolicyMissing()
    {
        $resolver = new MapResolver();

        $this->expectException(MissingPolicyException::class);
        $this->expectExceptionMessage('Policy for `TestApp\Authorization\Model\Entity\Article` has not been defined.');

        $resolver->getPolicy(new Article());
    }
}
