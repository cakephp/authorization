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
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Test\TestCase\Policy;

use Cake\TestSuite\TestCase;
use Phauthentic\Authorization\Policy\Exception\MissingPolicyException;
use Phauthentic\Authorization\Policy\MapResolver;
use Phauthentic\Authorization\Policy\ResolverCollection;
use Phauthentic\Authorization\Policy\ResolverInterface;
use TestApp\Model\Entity\Article;
use TestApp\Policy\ArticlePolicy;

class ResolverCollectionTest extends TestCase
{
    public function testEmptyCollection()
    {
        $collection = new ResolverCollection();

        $this->expectException(MissingPolicyException::class);

        $collection->getPolicy(new Article());
    }

    public function testMissingPolicy()
    {
        $resource = new Article();

        $exception = new MissingPolicyException();
        $exception = $exception->setMessageVars([get_class($resource)]);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects($this->once())
            ->method('getPolicy')
            ->with($resource)
            ->willThrowException($exception);

        $collection = new ResolverCollection([
            $resolver,
        ]);

        $this->expectException(MissingPolicyException::class);

        $collection->getPolicy($resource);
    }

    public function testGetPolicy()
    {
        $resource = new Article();
        $policy = new ArticlePolicy();

        $resolver1 = new MapResolver();
        $resolver2 = new MapResolver([Article::class => $policy]);

        $collection = new ResolverCollection([$resolver1, $resolver2]);

        $result = $collection->getPolicy($resource);
        $this->assertSame($policy, $result);
    }
}
