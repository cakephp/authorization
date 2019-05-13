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
use Authorization\Policy\MapResolver;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use TestApp\Model\Entity\Article;
use TestApp\Policy\ArticlePolicy;

class MapResolverTest extends TestCase
{
    public function testGetPolicyClassName()
    {
        $resolver = new MapResolver();

        $resolver->map(Article::class, ArticlePolicy::class);

        $result = $resolver->getPolicy(new Article());
        $this->assertInstanceOf(ArticlePolicy::class, $result);
    }

    public function testGetPolicyObject()
    {
        $resolver = new MapResolver();
        $policy = new ArticlePolicy();

        $resolver->map(Article::class, $policy);

        $result = $resolver->getPolicy(new Article());
        $this->assertSame($policy, $result);
    }

    public function testGetPolicyCallable()
    {
        $resolver = new MapResolver();
        $resource = new Article();
        $policy = new ArticlePolicy();

        $resolver->map(Article::class, function ($arg1, $arg2) use ($policy, $resolver, $resource) {
            $this->assertSame($resource, $arg1);
            $this->assertSame($resolver, $arg2);

            return $policy;
        });

        $result = $resolver->getPolicy($resource);
        $this->assertSame($policy, $result);
    }

    public function testMapMissingResource()
    {
        $resolver = new MapResolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource class `Foo` does not exist.');

        $resolver->map('Foo', 'Bar');
    }

    public function testMapInvalidPolicy()
    {
        $resolver = new MapResolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Policy must be a valid class name, an object or a callable, `array` given.');

        $resolver->map(Article::class, []);
    }

    public function testMapMissingPolicy()
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
        $this->expectExceptionMessage('Policy for `TestApp\Model\Entity\Article` has not been defined.');

        $resolver->getPolicy(new Article());
    }
}
