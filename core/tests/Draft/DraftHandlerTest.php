<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\Draft;

use Mockery as m;
use Xpressengine\Draft\DraftHandler;

class DraftHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testGet()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $mockEntity1 = m::mock('Xpressengine\Draft\DraftEntity');
        $mockEntity2 = m::mock('Xpressengine\Draft\DraftEntity');

        $mockUser = m::mock('Xpressengine\Member\Entities\MemberEntityInterface');
        $mockUser->shouldReceive('getId')->andReturn('userId');

        $auth->shouldReceive('user')->andReturn($mockUser);

        $repo->shouldReceive('fetch')->once()->with(['userId' => 'userId', 'key' => 'someKey'])
            ->andReturn([$mockEntity1, $mockEntity2]);

        $drafts = $instance->get('someKey');

        $this->assertEquals(2, count($drafts));
    }

    public function testGetById()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $mockEntity = m::mock('Xpressengine\Draft\DraftEntity');

        $repo->shouldReceive('find')->once()->with('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')->andReturn($mockEntity);

        $draft = $instance->getById('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');

        $this->assertEquals($mockEntity, $draft);
    }

    public function testSet()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $mockUser = m::mock('Xpressengine\Member\Entities\MemberEntityInterface');
        $mockUser->shouldReceive('getId')->andReturn('userId');

        $auth->shouldReceive('guest')->andReturn(false);
        $auth->shouldReceive('user')->andReturn($mockUser);

        $repo->shouldReceive('insert')->once()->with(m::on(function ($entity) {
            return (
                $entity->userId == 'userId'
                && $entity->key == 'someKey'
                && $entity->val == 'foo'
                && $entity->etc == serialize(['baz' => 'qux'])
            );
        }));

        $instance->set('someKey', 'foo', ['baz' => 'qux']);
    }

    public function testSetReturnsNullWhenUserIsGuest()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $mockUser = m::mock('Xpressengine\Member\Entities\MemberEntityInterface');
        $mockUser->shouldReceive('getId')->andReturn('userId');

        $auth->shouldReceive('guest')->andReturn(true);


        $result = $instance->set('someKey', ['foo' => 'bar']);

        $this->assertNull($result);
    }

    public function testPut()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $repo->shouldReceive('find')->once()->with('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')->andReturnNull();

        $this->assertNull($instance->put('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'foo'));


        $mockEntity = m::mock('Xpressengine\Draft\DraftEntity');
        $repo->shouldReceive('find')->once()->with('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')->andReturn($mockEntity);
        $repo->shouldReceive('update')->once()->with($mockEntity)->andReturn($mockEntity);

        $instance->put('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'foo', ['baz' => 'qux']);
    }

    public function testRemove()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $mockEntity = m::mock('Xpressengine\Draft\DraftEntity');

        $repo->shouldReceive('delete')->once()->with($mockEntity);

        $instance->remove($mockEntity);
    }

    public function testExists()
    {
        list($repo, $auth) = $this->getMocks();
        $instance = new DraftHandler($repo, $auth);

        $mockEntity1 = m::mock('Xpressengine\Draft\DraftEntity');
        $mockEntity2 = m::mock('Xpressengine\Draft\DraftEntity');

        $mockUser = m::mock('Xpressengine\Member\Entities\MemberEntityInterface');
        $mockUser->shouldReceive('getId')->andReturn('userId');

        $auth->shouldReceive('user')->andReturn($mockUser);

        $repo->shouldReceive('fetch')->once()->with(['userId' => 'userId', 'key' => 'someKey'])->andReturn([$mockEntity1, $mockEntity2]);

        $this->assertTrue($instance->exists('someKey'));
    }

    private function getMocks()
    {
        return [
            m::mock('Xpressengine\Draft\DraftRepository'),
            m::mock('Illuminate\Auth\AuthManager')
        ];
    }
}
