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
use Authorization\Policy\ResolverCollection;
use Authorization\Policy\ResolverInterface;
use Cake\TestSuite\TestCase;
use TestApp\Authorization\Model\Entity\Article;
use TestApp\Authorization\Policy\Model\Entity\Article as ArticlePolicy;

class ResolverCollectionTest extends TestCase
{
    public function testEmptyCollection()
    {
        $collection = new ResolverCollection;

        $this->expectException(MissingPolicyException::class);

        $collection->getPolicy(new Article);
    }

    public function testMissingPolicy()
    {
        $resource = new Article;

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects($this->once())
            ->method('getPolicy')
            ->with($resource)
            ->willThrowException(new MissingPolicyException());

        $collection = new ResolverCollection([
            $resolver
        ]);

        $this->expectException(MissingPolicyException::class);

        $collection->getPolicy($resource);
    }

    public function testGetPolicy()
    {
        $resource = new Article;
        $policy = new ArticlePolicy;

        $resolver1 = $this->createMock(ResolverInterface::class);
        $resolver1->expects($this->once())
            ->method('getPolicy')
            ->with($resource)
            ->willThrowException(new MissingPolicyException());

        $resolver2 = $this->createMock(ResolverInterface::class);
        $resolver2->expects($this->once())
            ->method('getPolicy')
            ->with($resource)
            ->willReturn($policy);

        $collection = new ResolverCollection([
            $resolver1, $resolver2
        ]);

        $result = $collection->getPolicy($resource);
        $this->assertSame($policy, $result);
    }
}
