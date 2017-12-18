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
namespace Authorization\Test\TestCase;

use Authorization\AuthorizationService;
use Authorization\IdentityDecorator;
use Authorization\Policy\BeforePolicyInterface;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\ResolverInterface;
use Cake\TestSuite\TestCase;
use TestApp\Authorization\Model\Entity\Article;
use TestApp\Authorization\Policy\Model\Entity\Article as ArticlePolicy;

class AuthorizationServiceTest extends TestCase
{
    public function testCan()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $entity = new Article();
        $policy = new ArticlePolicy();

        $resolver->expects($this->once())
            ->method('getPolicy')
            ->with($entity)
            ->willReturn($policy);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin'
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertTrue($result);
    }

    public function testBeforeFalse()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $entity = new Article();
        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $resolver->expects($this->once())
            ->method('getPolicy')
            ->with($entity)
            ->willReturn($policy);

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity)
            ->willReturn(false);

        $policy->expects($this->never())
            ->method('canAdd');

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin'
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertFalse($result);
    }

    public function testBeforeTrue()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $entity = new Article();
        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $resolver->expects($this->once())
            ->method('getPolicy')
            ->with($entity)
            ->willReturn($policy);

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity)
            ->willReturn(true);

        $policy->expects($this->once())
            ->method('canAdd')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity)
            ->willReturn(true);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin'
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertTrue($result);
    }

    public function testMissingMethod()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $entity = new Article();
        $policy = new ArticlePolicy();

        $resolver->expects($this->once())
            ->method('getPolicy')
            ->with($entity)
            ->willReturn($policy);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin'
        ]);

        $this->expectException(MissingMethodException::class);
        $this->expectExceptionMessage('Method `canModify` for invoking action `modify` has not been defined in `TestApp\Authorization\Policy\Model\Entity\Article`.');

        $service->can($user, 'modify', $entity);
    }
}
