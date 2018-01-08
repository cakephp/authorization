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
namespace Authorization\Test\TestCase\Controller\Component;

use Authorization\AuthorizationService;
use Authorization\Controller\Component\AuthorizationComponent;
use Authorization\IdentityDecorator;
use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use BadMethodCallException;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\Network\Exception\ForbiddenException;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use stdClass;
use TestApp\Model\Entity\Article;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Policy\ArticlesTablePolicy;
use UnexpectedValueException;

/**
 * AuthorizationComponentTest class
 */
class AuthorizationComponentTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['id' => 1, 'role' => 'user']);

        $request = new ServerRequest([
            'params' => ['controller' => 'Articles', 'action' => 'edit'],
        ]);
        $request = $request->withAttribute('identity', $identity);

        $this->Controller = new Controller($request);
        $this->ComponentRegistry = new ComponentRegistry($this->Controller);
        $this->Auth = new AuthorizationComponent($this->ComponentRegistry);
    }

    public function testAuthorizeUnresolvedPolicy()
    {
        $this->expectException(MissingPolicyException::class);

        $this->Auth->authorize(new stdClass);
    }

    public function testAuthorizeFailedCheck()
    {
        $this->expectException(ForbiddenException::class);

        $article = new Article(['user_id' => 99]);
        $this->Auth->authorize($article);
    }

    public function testAuthorizeSuccessCheckImplictAction()
    {
        $article = new Article(['user_id' => 1]);
        $this->assertNull($this->Auth->authorize($article));
    }

    public function testAuthorizeSuccessCheckExplictAction()
    {
        $article = new Article(['user_id' => 1]);
        $this->assertNull($this->Auth->authorize($article, 'edit'));
    }

    public function testAuthorizeBadIdentity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegexp('/Expected that `identity` would be/');

        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', 'derp');

        $article = new Article(['user_id' => 1]);
        $this->Auth->authorize($article);
    }

    public function testAuthorizeCustomException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->Auth->config('forbiddenException', BadMethodCallException::class);

        $article = new Article(['user_id' => 99]);
        $this->Auth->authorize($article);
    }

    public function testAuthorizeModelSuccess()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', $identity);

        $result = $this->Auth->authorizeModel();
        $this->assertNull($result);
    }

    public function testAuthorizeModelFailure()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => false]);
        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', $identity);

        $this->expectException(ForbiddenException::class);
        $this->Auth->authorizeModel();
    }

    public function testAuthorizeModelEnabled()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', $identity);

        $this->Auth->setConfig('actionMap', ['edit' => true]);
        $result = $this->Auth->authorizeModel();
        $this->assertNull($result);
    }

    public function testAuthorizeModelDisabled()
    {
        $policy = $this->createMock(ArticlesTablePolicy::class);
        $service = new AuthorizationService(new MapResolver([
            ArticlesTable::class => $policy
        ]));

        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', $identity);

        $policy->expects($this->never())
            ->method('canEdit');

        $this->Auth->setConfig('actionMap', ['edit' => false]);
        $result = $this->Auth->authorizeModel();
        $this->assertNull($result);
    }

    public function testAuthorizeModelMapped()
    {
        $policy = $this->createMock(ArticlesTablePolicy::class);
        $service = new AuthorizationService(new MapResolver([
            ArticlesTable::class => $policy
        ]));

        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', $identity);

        $policy->expects($this->never())
            ->method('canEdit');

        $policy->expects($this->once())
            ->method('canModify')
            ->willReturn(true);

        $this->Auth->setConfig('actionMap', ['edit' => 'modify']);
        $result = $this->Auth->authorizeModel();
        $this->assertNull($result);
    }

    public function testAuthorizeModelInvalid()
    {
        $service = new AuthorizationService(new OrmResolver());
        $identity = new IdentityDecorator($service, ['can_edit' => true]);
        $this->Controller->request = $this->Controller->request
            ->withAttribute('identity', $identity);

        $this->Auth->setConfig('actionMap', ['edit' => new stdClass]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid action type for `edit`. Expected `string`, `null` or `bool`, got `stdClass`.');
        $this->Auth->authorizeModel();
    }

    public function testImplementedEvents()
    {
        $events = $this->Auth->implementedEvents();
        $this->assertEquals([
            'Controller.initialize' => 'authorizeModel'
        ], $events);
    }

    public function testImplementedCustom()
    {
        $this->Auth->setConfig('authorizationEvent', 'Controller.startup');
        $events = $this->Auth->implementedEvents();
        $this->assertEquals([
            'Controller.startup' => 'authorizeModel'
        ], $events);
    }

    public function testImplementedDisabled()
    {
        $this->Auth->setConfig('authorizeModel', false);
        $events = $this->Auth->implementedEvents();
        $this->assertEquals([], $events);
    }
}
