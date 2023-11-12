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
use Authorization\Policy\BeforeScopeInterface;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;
use Cake\Datasource\QueryInterface;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Article;
use TestApp\Model\Table\ArticlesTable;
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

    public function testCanWithAdditionalParams()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $innerService = function () {
            return true;
        };

        $result = $service->can($user, 'withService', new Article(), $innerService);
        $this->assertTrue($result);
    }

    public function testCanWithAdditionalNamedParams()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $innerService1 = function () {
            return true;
        };

        $innerService2 = function () {
            return false;
        };

        $result = $service->can(user: $user, action: 'withMultipleServices', resource: new Article(), service2: $innerService2, service1: $innerService1);
        $this->assertFalse($result);
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

    public function testCanWithResultAndAdditionalParams()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $innerService = function () {
            return true;
        };

        $result = $service->canResult($user, 'withService', new Article(), $innerService);
        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testCanWithResultAndAdditionalNamedParams()
    {
        $resolver = new MapResolver([
            Article::class => ArticlePolicy::class,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $innerService1 = function () {
            return true;
        };

        $innerService2 = function () {
            return false;
        };

        $result = $service->canResult(user: $user, action: 'withMultipleServices', resource: new Article(), service2: $innerService2, service1: $innerService1);
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

    public function testCallingMagicCanCallPolicy()
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

    public function testCallingMagicScopeCallPolicy()
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
        $this->assertTrue($service->applyScope($user, 'this', $article));
        $this->assertFalse($service->applyScope($user, 'somethingElse', $article));
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
        $service->applyScope($user, 'nope', $article);
    }

    public function testApplyScopeAdditionalArguments()
    {
        $service = new AuthorizationService(new OrmResolver());
        $user = new IdentityDecorator($service, [
            'id' => 9,
            'role' => 'admin',
        ]);

        $articles = new ArticlesTable();
        $query = $this->createMock(QueryInterface::class);
        $query->method('getRepository')
            ->willReturn($articles);

        $query->expects($this->exactly(2))
            ->method('where')
            ->with([
                'identity_id' => 9,
                'firstArg' => 'first argument',
                'secondArg' => false,
            ])
            ->willReturn($query);

        $result = $service->applyScope($user, 'additionalArguments', $query, 'first argument', false);
        $this->assertInstanceOf(QueryInterface::class, $result);
        $this->assertSame($query, $result);

        // Test with named args as well
        $result = $service->applyScope($user, 'additionalArguments', $query, firstArg: 'first argument', secondArg: false);
        $this->assertInstanceOf(QueryInterface::class, $result);
        $this->assertSame($query, $result);
    }

    public function testBeforeFalse()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforePolicyInterface::class)
            ->onlyMethods(['before'])
            ->addMethods(['canAdd'])
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
            ->onlyMethods(['before'])
            ->addMethods(['canAdd'])
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
            ->onlyMethods(['before'])
            ->addMethods(['canAdd'])
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
            ->onlyMethods(['before'])
            ->addMethods(['canAdd'])
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
            ->onlyMethods(['before'])
            ->addMethods(['canAdd'])
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

    public function testBeforeScopeNonNull()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforeScopeInterface::class)
            ->onlyMethods(['beforeScope'])
            ->addMethods(['scopeIndex'])
            ->getMock();

        $policy->expects($this->once())
            ->method('beforeScope')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'index')
            ->willReturn('foo');

        $policy->expects($this->never())
            ->method('scopeIndex');

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->applyScope($user, 'index', $entity);
        $this->assertEquals('foo', $result);
    }

    public function testBeforeScopeNull()
    {
        $entity = new Article();

        $policy = $this->getMockBuilder(BeforeScopeInterface::class)
            ->onlyMethods(['beforeScope'])
            ->addMethods(['scopeIndex'])
            ->getMock();

        $policy->expects($this->once())
            ->method('beforeScope')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity, 'index')
            ->willReturn(null);

        $policy->expects($this->once())
            ->method('scopeIndex')
            ->with($this->isInstanceOf(IdentityDecorator::class), $entity)
            ->willReturn('bar');

        $resolver = new MapResolver([
            Article::class => $policy,
        ]);

        $service = new AuthorizationService($resolver);

        $user = new IdentityDecorator($service, [
            'role' => 'admin',
        ]);

        $result = $service->applyScope($user, 'index', $entity);
        $this->assertEquals('bar', $result);
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
