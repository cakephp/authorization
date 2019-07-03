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
namespace Authorization\Test\TestCase;

use Authorization\AuthorizationService;
use Authorization\IdentityDecorator;
use Authorization\Policy\BeforePolicyInterface;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\MapResolver;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;
use Cake\TestSuite\TestCase;
use RuntimeException;
use TestApp\Model\Entity\Article;
use TestApp\Policy\ArticlePolicy;
use TestApp\Policy\MagicCallPolicy;

class AuthorizationServiceTest extends TestCase
{
    public function testNullUserCan()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = null;

        $result = $service->can($user, 'view', new Article());
        $this->assertFalse($result);

        $result = $service->can($user, 'view', new Article(['visibility' => 'public']));
        $this->assertTrue($result);
    }

    public function testCan()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->can($user, 'add', new Article());
        $this->assertTrue($result);
    }

    public function testCanWithResult()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->canResult($user, 'publish', new Article());
        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testAuthorizationCheckedWithCan()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);
        $service = new AuthorizationService($resolver);
        $this->assertFalse($service->authorizationChecked());

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $service->can($user, 'add', new Article());
        $this->assertTrue($service->authorizationChecked());
    }

    public function testCallingMagicCallPolicy()
    {
        $resolver = new MapResolver([
            Article::class => MagicCallPolicy::class,
        ]);
        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'id' => 9,
            'role' => 'admin',
        ]);

        $article = new Article();
        $this->assertTrue($service->can($user, 'doThat', $article));
        $this->assertFalse($service->can($user, 'cantDoThis', $article));
    }

    public function testAuthorizationCheckedWithApplyScope()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);
        $service = new AuthorizationService($resolver);
        $this->assertFalse($service->authorizationChecked());

        $user = new IdentityDecorator($service, [
            'id' => 9,
            'role' => 'admin',
        ]);

        $service->applyScope($user, 'index', new Article());
        $this->assertTrue($service->authorizationChecked());
    }

    public function testSkipAuthorization()
    {
        $resolver = new MapResolver([]);
        $service = new AuthorizationService($resolver);
        $this->assertFalse($service->authorizationChecked());

        $service->skipAuthorization();
        $this->assertTrue($service->authorizationChecked());
    }

    public function testApplyScope()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);
        $service = new AuthorizationService($resolver);
        $user = new IdentityDecorator($service, [
            'id' => 9,
            'role' => 'admin',
        ]);

        $article = new Article();
        $result = $service->applyScope($user, 'index', $article);
        $this->assertSame($article, $result);
        $this->assertSame($article->user_id, $user->getOriginalData()['id']);
    }

    public function testApplyScopeMethodMissing()
    {
        $this->expectException(MissingMethodException::class);

        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);
        $service = new AuthorizationService($resolver);
        $user = new IdentityDecorator($service, [
            'id' => 9,
            'role' => 'admin',
        ]);

        $article = new Article();
        $result = $service->applyScope($user, 'nope', $article);
    }

    public function testBeforeFalse()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'add')
            ->willReturn(false);

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $policy->expects($this->never())
            ->method('canAdd');

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertFalse($result);
    }

    public function testBeforeTrue()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'add')
            ->willReturn(true);

        $policy->expects($this->never())
            ->method('canAdd');

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertTrue($result);
    }

    public function testBeforeNull()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'add')
            ->willReturn(null);

        $policy->expects($this->once())
            ->method('canAdd')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity)
            ->willReturn(true);

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertTrue($result);
    }

    public function testBeforeResultTrue()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'add')
            ->willReturn(new Result(true));

        $policy->expects($this->never())
            ->method('canAdd');

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertTrue($result);
    }

    public function testBeforeResultFalse()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'add')
            ->willReturn(new Result(false));

        $policy->expects($this->never())
            ->method('canAdd');

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->can($user, 'add', $entity);
        $this->assertFalse($result);
    }

    public function testBeforeOther()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->setMethods(['before', 'canAdd'])
            ->getMock();

        $policy->expects($this->once())
            ->method('before')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'add')
            ->willReturn('foo');

        $policy->expects($this->never())
            ->method('canAdd');

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pre-authorization check must return `Authorization\Policy\ResultInterface`, `bool` or `null`.');

        $service->can($user, 'add', $entity);
    }

    public function testMissingMethod()
    {
        $entity = new Article();

        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $this->expectException(MissingMethodException::class);
        $this->expectExceptionMessage('Method `canDisable` for invoking action `disable` has not been defined in `TestApp\Policy\ArticlePolicy`.');

        $service->can($user, 'disable', $entity);
    }
}
