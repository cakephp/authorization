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
namespace Authorization\Test\TestCase\Controller\Component;

use Authorization\AuthorizationService;
use Authorization\Controller\Component\AuthorizationComponent;
use Authorization\Exception\ForbiddenException;
use Authorization\IdentityDecorator;
use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResultInterface;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Datasource\QueryInterface;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use stdClass;
use TestApp\Model\Entity\Article;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Policy\ArticlePolicy;
use TestApp\Policy\ArticlesTablePolicy;
use TestApp\Policy\StringResolver;
use UnexpectedValueException;

/**
 * AuthorizationComponentTest class
 */
class AuthorizationComponentTest extends TestCase
{
    /**
     * @var \Authorization\Controller\Component\AuthorizationComponent
     */
    protected $Auth;

    /**
     * @var \Cake\Http\ServerRequest
     */
    protected $request;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['id' => 1, 'role' => 'user']);

        $request = new ServerRequest([
            'params' => ['controller' => 'Articles', 'action' => 'edit'],
        ]);
        $this->request = $request
            ->withAttribute('authorization', $service)
            ->withAttribute('identity', $identity);

        $this->Controller = new Controller($this->request);
        $this->ComponentRegistry = new ComponentRegistry($this->Controller);
        $this->Auth = new AuthorizationComponent($this->ComponentRegistry);
    }

    public function testNullIdentityForbiddenException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Identity is not authorized to perform `view` on `TestApp\Model\Entity\Article`.');

        $service = new AuthorizationService(new OrmResolver());
        $request = new ServerRequest([
            'params' => ['controller' => 'Articles', 'action' => 'view'],
        ]);
        $request = $request
            ->withAttribute('authorization', $service);

        $article = new Article(['visibility' => 'private']);
        $controller = new Controller($request);
        $componentRegistry = new ComponentRegistry($controller);
        $auth = new AuthorizationComponent($componentRegistry);

        $auth->access($article);
    }

    public function testNullIdentityAllowed()
    {
        $service = new AuthorizationService(new OrmResolver());
        $request = new ServerRequest([
            'params' => ['controller' => 'Articles', 'action' => 'view'],
        ]);
        $request = $request
            ->withAttribute('authorization', $service);

        $article = new Article(['visibility' => 'public']);
        $controller = new Controller($request);
        $componentRegistry = new ComponentRegistry($controller);
        $auth = new AuthorizationComponent($componentRegistry);

        $this->assertNull($auth->access($article));
    }

    public function testAccessWithUnresolvedPolicy()
    {
        $this->expectException(MissingPolicyException::class);

        $this->Auth->access(new stdClass());
    }

    public function testAccessFailure()
    {
        $this->expectException(ForbiddenException::class);

        $article = new Article(['user_id' => 99]);
        $this->Auth->access($article);
    }

    public function testAccessFailureWithResult()
    {
        $this->expectException(ForbiddenException::class);

        $article = new Article(['user_id' => 1, 'visibility' => 'public']);
        try {
            $this->Auth->access($article, 'publish');
        } catch (ForbiddenException $e) {
            $result = $e->getResult();
            $this->assertSame('public', $result->getReason());
            $this->assertFalse($result->getStatus());

            throw $e;
        }
    }

    public function testAccessFailureWithStringResolver()
    {
        // Reset the system to use the string resolver
        $service = new AuthorizationService(new StringResolver());
        $identity = new IdentityDecorator($service, ['can_index' => false]);
        $request = new ServerRequest([
            'params' => ['controller' => 'Articles', 'action' => 'index'],
        ]);

        $request = $request
            ->withAttribute('authorization', $service)
            ->withAttribute('identity', $identity);

        $this->Controller = new Controller($request);
        $this->ComponentRegistry = new ComponentRegistry($this->Controller);
        $this->Auth = new AuthorizationComponent($this->ComponentRegistry);

        $this->expectException(ForbiddenException::class);

        $this->Auth->access('ArticlesTable');
    }

    public function testAccessSuccessWithImplicitAction()
    {
        $article = new Article(['user_id' => 1]);
        $this->assertNull($this->Auth->access($article));
    }

    public function testAccessSuccessWithMappedAction()
    {
        $policy = $this->createMock(ArticlePolicy::class);
        $service = new AuthorizationService(new MapResolver([
            Article::class => $policy,
        ]));

        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $policy->expects($this->never())
            ->method('canEdit');

        $policy->expects($this->once())
            ->method('canModify')
            ->willReturn(true);

        $article = new Article(['user_id' => 1]);

        $this->Auth->setConfig('actionMap', ['edit' => 'modify']);
        $this->assertNull($this->Auth->access($article));
    }

    public function testAccessSuccessWithStringResolver()
    {
        // Reset the system to use the string resolver
        $service = new AuthorizationService(new StringResolver());
        $identity = new IdentityDecorator($service, ['can_index' => true]);
        $request = new ServerRequest([
            'params' => ['controller' => 'Articles', 'action' => 'index'],
        ]);

        $request = $request
            ->withAttribute('authorization', $service)
            ->withAttribute('identity', $identity);

        $this->Controller = new Controller($request);
        $this->ComponentRegistry = new ComponentRegistry($this->Controller);
        $this->Auth = new AuthorizationComponent($this->ComponentRegistry);

        $this->assertNull($this->Auth->access('ArticlesTable'));
    }

    public function testAccessSuccessfulWithResult()
    {
        $article = new Article(['user_id' => 1]);
        $this->assertNull($this->Auth->access($article, 'publish'));
    }

    /**
     * Test deprecated authorize().
     *
     * @return void
     */
    public function testAuthorizeDeprecation()
    {
        $this->deprecated(function () {
            $article = new Article(['user_id' => 1]);
            $this->assertNull($this->Auth->authorize($article, 'publish'));
        });
    }

    public function testApplyScopeImplicitAction()
    {
        $articles = new ArticlesTable();
        $query = $this->createMock(QueryInterface::class);
        $query->method('getRepository')
            ->willReturn($articles);

        $query->expects($this->once())
            ->method('where')
            ->with([
                'user_id' => 1,
            ])
            ->willReturn($query);

        $result = $this->Auth->applyScope($query);

        $this->assertInstanceOf(QueryInterface::class, $result);
        $this->assertSame($query, $result);
    }

    public function testApplyScopeMappedAction()
    {
        $articles = new ArticlesTable();
        $query = $this->createMock(QueryInterface::class);
        $query->method('getRepository')
            ->willReturn($articles);

        $query->expects($this->once())
            ->method('where')
            ->with([
                'identity_id' => 1,
            ])
            ->willReturn($query);

        $this->Auth->setConfig('actionMap', ['edit' => 'modify']);
        $result = $this->Auth->applyScope($query);

        $this->assertInstanceOf(QueryInterface::class, $result);
        $this->assertSame($query, $result);
    }

    public function testApplyScopExplicitAction()
    {
        $articles = new ArticlesTable();
        $query = $this->createMock(QueryInterface::class);
        $query->method('getRepository')
            ->willReturn($articles);

        $query->expects($this->once())
            ->method('where')
            ->with([
                'identity_id' => 1,
            ])
            ->willReturn($query);

        $result = $this->Auth->applyScope($query, 'modify');

        $this->assertInstanceOf(QueryInterface::class, $result);
        $this->assertSame($query, $result);
    }

    public function testCheckSuccessWithExplicitAction()
    {
        $article = new Article(['user_id' => 1]);
        $this->assertNull($this->Auth->access($article, 'edit'));
    }

    public function testAccessWithBadIdentity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Expected that `identity` would be/');

        $this->Controller->setRequest($this->request
            ->withAttribute('identity', 'derp'));

        $article = new Article(['user_id' => 1]);
        $this->Auth->access($article);
    }

    public function testCheckActionSuccess()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $result = $this->Auth->checkAction();
        $this->assertNull($result);
    }

    public function testCheckActionFailure()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => false]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $this->Auth->setConfig('authorizeModel', ['edit']);
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Identity is not authorized to perform `edit` on `TestApp\Model\Table\ArticlesTable`.');
        $this->Auth->checkAction();
    }

    public function testCheckActionAllDisabled()
    {
        $policy = $this->createMock(ArticlesTablePolicy::class);
        $service = new AuthorizationService(new MapResolver([
            ArticlesTable::class => $policy,
        ]));

        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $policy->expects($this->never())
            ->method('canEdit');

        $result = $this->Auth->checkAction();
        $this->assertNull($result);
    }

    public function testCheckActionEnabled()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $this->Auth->setConfig('authorizeModel', ['edit']);
        $result = $this->Auth->checkAction();
        $this->assertNull($result);
    }

    public function testCheckActionAliases()
    {
        $policy = $this->createMock(ArticlesTablePolicy::class);
        $service = new AuthorizationService(new MapResolver([
            ArticlesTable::class => $policy,
        ]));

        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $policy->expects($this->never())
            ->method('canEdit');

        $policy->expects($this->once())
            ->method('canModify')
            ->willReturn(true);

        $this->Auth->setConfig('authorizeModel', ['edit']);
        $this->Auth->setConfig('actionMap', ['edit' => 'modify']);
        $result = $this->Auth->checkAction();
        $this->assertNull($result);
    }

    public function testCheckActionInvalidAlias()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->setRequest($this->request
            ->withAttribute('identity', $identity));

        $this->Auth->setConfig('authorizeModel', ['edit']);
        $this->Auth->setConfig('actionMap', ['edit' => new stdClass()]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid action type for `edit`. Expected `string` or `null`, got `stdClass`.');
        $this->Auth->checkAction();
    }

    /**
     * Test deprecated authorizeAction().
     *
     * @return void
     */
    public function testAuthorizeActionDeprecated()
    {
        $this->deprecated(function () {
            $service = new AuthorizationService(new OrmResolver());
            $identity = new IdentityDecorator($service, ['can_edit' => true]);
            $this->Controller->setRequest($this->request
                ->withAttribute('identity', $identity));

            $result = $this->Auth->authorizeAction();
            $this->assertNull($result);
        });
    }

    public function testImplementedEvents()
    {
        $events = $this->Auth->implementedEvents();
        $this->assertEquals([
            'Controller.startup' => 'checkAction',
        ], $events);
    }

    public function testImplementedCustom()
    {
        $this->Auth->setConfig('authorizationEvent', 'Controller.initialize');
        $events = $this->Auth->implementedEvents();
        $this->assertEquals([
            'Controller.initialize' => 'checkAction',
        ], $events);
    }

    public function testSkipAuthorization()
    {
        $service = $this->Controller->getRequest()->getAttribute('authorization');
        $this->assertFalse($service->authorizationChecked());

        $this->Auth->skipAuthorization();
        $this->assertTrue($service->authorizationChecked());
    }

    public function testSkipAuthorizationBadService()
    {
        $this->Controller->setRequest($this->request
            ->withAttribute('authorization', 'derp'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Expected that `authorization` would be/');
        $this->Auth->skipAuthorization();
    }

    public function testCheckActionNotSkipped()
    {
        $service = $this->Controller->getRequest()->getAttribute('authorization');

        $this->Auth->checkAction();
        $this->assertFalse($service->authorizationChecked());
    }

    public function testCheckActionSkipped()
    {
        $service = $this->Controller->getRequest()->getAttribute('authorization');

        $this->Auth->setConfig('skipAuthorization', ['edit']);
        $this->Auth->checkAction();
        $this->assertTrue($service->authorizationChecked());
    }

    public function testCan()
    {
        $article = new Article(['user_id' => 1]);
        $this->assertTrue($this->Auth->can($article));
        $this->assertTrue($this->Auth->can($article, 'delete'));

        $article = new Article(['user_id' => 2]);
        $this->assertFalse($this->Auth->can($article));
        $this->assertFalse($this->Auth->can($article, 'delete'));
    }

    public function testCanWithResult()
    {
        $article = new Article(['user_id' => 1]);
        $result = $this->Auth->canResult($article, 'publish');
        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testAuthorizeModel()
    {
        $this->Auth->authorizeModel('foo', 'bar');
        $this->assertEquals(['foo', 'bar'], $this->Auth->getConfig('authorizeModel'));

        $this->Auth->authorizeModel('baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $this->Auth->getConfig('authorizeModel'));
    }

    public function testMapAction()
    {
        $this->Auth->mapAction('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $this->Auth->getConfig('actionMap'));

        $this->Auth->mapAction('bar', 'baz');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $this->Auth->getConfig('actionMap'));
    }

    public function testMapActions()
    {
        $this->Auth->mapActions(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $this->Auth->getConfig('actionMap'));

        $this->Auth->mapActions(['bar' => 'baz']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $this->Auth->getConfig('actionMap'));

        $this->Auth->mapActions(['foo' => 'baz'], true);
        $this->assertEquals(['foo' => 'baz'], $this->Auth->getConfig('actionMap'));
    }
}
