<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\User;

use Mockery;
use Xpressengine\User\Models\PendingEmail;
use Xpressengine\User\Models\User;
use Xpressengine\User\Repositories\PendingEmailRepository;

class PendingEmailRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testConstruct()
    {
        /** @var PendingEmailRepository $repo */
        $repo = new PendingEmailRepository('foo');
        $this->assertInstanceOf('Xpressengine\User\Repositories\PendingEmailRepository', $repo);
    }

    public function testCreate()
    {
        $data = [
            'foo' => 'foo'
        ];

        $repo = $this->makeRepository();

        $user = $this->makeUser();

        $email = $this->makeEmail();
        $self = $this;
        $email->shouldReceive('create')->with(Mockery::on(function($d) use($self){
            $self->assertArrayHasKey('confirmationCode', $d);
            return true;
        }))->once()->andReturnSelf();

        $relation = Mockery::mock('\Illuminate\Database\Eloquent\Relations\HasOne');
        $relation->shouldReceive('save')->with($email)->andReturnNull();

        $user->shouldReceive('pendingEmail')->once()->andReturn($relation);

        $repo->shouldReceive('createModel')->andReturn($email);

        /** @var PendingEmailRepository $repo */
        /** @var User $user */
        $this->assertEquals($email, $repo->create($user, $data));
    }

    public function testFindByUserId()
    {
        $userId = '123';
        $repo = $this->makeRepository();

        $email = $this->makeEmail();
        $email->shouldReceive('newQuery')->once()->andReturnSelf();
        $email->shouldReceive('where')->once()->with('userId', $userId)->andReturnSelf();
        $email->shouldReceive('first')->once()->withNoArgs()->andReturnSelf();

        $repo->shouldReceive('createModel')->andReturn($email);

        /** @var PendingEmailRepository $repo */
        /** @var User $user */
        $this->assertEquals($email, $repo->findByUserId($userId));
    }

    public function testFindByAddress()
    {
        $address = 'email@address.com';
        $expected = 'foo';
        $repo = $this->makeRepository();

        $email = $this->makeEmail();
        $email->shouldReceive('newQuery')->once()->andReturnSelf();
        $email->shouldReceive('where')->once()->with('address', $address)->andReturnSelf();
        $email->shouldReceive('first')->once()->withNoArgs()->andReturn($expected);

        $repo->shouldReceive('createModel')->andReturn($email);

        /** @var PendingEmailRepository $repo */
        /** @var User $user */
        $this->assertEquals($expected, $repo->findByAddress($address));
    }

    public function testDeleteByUserIds()
    {
        $userId = '123';
        $repo = $this->makeRepository();

        $email = $this->makeEmail();
        $email->shouldReceive('newQuery')->once()->andReturnSelf();
        $email->shouldReceive('whereIn')->once()->with('userId', ['123'])->andReturnSelf();
        $email->shouldReceive('delete')->once()->withNoArgs()->andReturn(2);

        $repo->shouldReceive('createModel')->andReturn($email);

        /** @var PendingEmailRepository $repo */
        /** @var User $user */
        $this->assertEquals(2, $repo->deleteByUserIds($userId));
    }

    /**
     * getRepository
     *
     * @param null $model
     *
     * @return Mockery\MockInterface
     */
    protected function makeRepository()
    {
        $repo = Mockery::mock(PendingEmailRepository::class)->makePartial();
        return $repo;
    }

    /**
     * makeUser
     *
     * @return Mockery\MockInterface
     */
    private function makeUser()
    {
        return Mockery::mock(User::class);
    }

    /**
     * makeEmail
     *
     * @return Mockery\MockInterface
     */
    private function makeEmail()
    {
        return Mockery::mock(PendingEmail::class);
    }


}

