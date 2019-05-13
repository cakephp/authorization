<?php
declare(strict_types=1);

namespace Authorization\Test\TestCase;

use ArrayObject;
use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityDecorator;
use BadMethodCallException;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use stdClass;
use TestApp\Model\Entity\Article;

/**
 * Test case for IdentityDecorator.
 */
class IdentityDecoratorTest extends TestCase
{
    public function constructorDataProvider()
    {
        return [
            'array' => [
                ['id' => 1],
            ],
            'ArrayAccess' => [
                new ArrayObject(['id' => 1]),
            ],
            'Entity' => [
                new Article(['id' => 1]),
            ],
        ];
    }

    /**
     * @dataProvider constructorDataProvider
     */
    public function testConstructorAccepted($data)
    {
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, $data);
        $this->assertSame($data['id'], $identity['id']);
    }

    public function testConstructorInvalidData()
    {
        $this->expectException(InvalidArgumentException::class);
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        new IdentityDecorator($auth, 'bad');
    }

    public function testCanDelegation()
    {
        $resource = new stdClass();
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, ['id' => 1]);

        $auth->expects($this->once())
            ->method('can')
            ->with($identity, 'update', $resource)
            ->will($this->returnValue(true));
        $this->assertTrue($identity->can('update', $resource));
    }

    public function testApplyScopeDelegation()
    {
        $resource = new stdClass();
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, ['id' => 1]);

        $auth->expects($this->once())
            ->method('applyScope')
            ->with($identity, 'update', $resource)
            ->will($this->returnValue(true));
        $this->assertTrue($identity->applyScope('update', $resource));
    }

    public function testCall()
    {
        $data = new Article(['id' => 1]);
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, $data);
        $this->assertFalse(method_exists($identity, 'isDirty'), 'method not defined on decorator');
        $this->assertTrue($identity->isDirty('id'), 'method is callable though.');
    }

    public function testCallArray()
    {
        $this->expectException(BadMethodCallException::class);
        $data = ['id' => 1];
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, $data);
        $identity->boom();
    }

    public function testArrayAccessImplementation()
    {
        $data = new ArrayObject(['id' => 1]);
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, $data);

        $this->assertTrue(isset($identity['id']));
        $this->assertFalse(isset($identity['nope']));
        $this->assertSame(1, $identity['id']);

        unset($identity['id']);
        $this->assertFalse(isset($identity['id']), 'Properties can be unset');

        $identity['id'] = 99;
        $this->assertSame(99, $identity['id'], 'Properties can be set.');
    }

    public function testGetOriginalData()
    {
        $data = ['id' => 2];
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, $data);
        $this->assertSame($data, $identity->getOriginalData());
    }

    public function testGetOriginalDataWrappedObjectHasOriginalData()
    {
        $data = ['id' => 2];
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $inner = new IdentityDecorator($auth, $data);
        $identity = new IdentityDecorator($auth, $inner);
        $this->assertSame($data, $identity->getOriginalData());
    }

    public function testGetProperty()
    {
        $data = new Article(['id' => 2]);
        $auth = $this->createMock(AuthorizationServiceInterface::class);
        $identity = new IdentityDecorator($auth, $data);

        $this->assertTrue(isset($identity->id));
        $this->assertSame($data->id, $identity->id);

        $this->assertFalse(isset($identity->unknown));
        $this->assertNull($identity->unknown);
    }
}
