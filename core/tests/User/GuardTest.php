<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\User;

use Illuminate\Cookie\CookieJar;
use Mockery as m;
use Symfony\Component\HttpFoundation\Request;
use Xpressengine\User\Guard;
use Xpressengine\User\Models\Guest;

class GuardTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testIsAuthedReturnsTrueWhenUserIsNotNull()
    {
        $user = m::mock(\Xpressengine\User\Authenticatable::class);
        $mock = $this->getGuard();
        $mock->setUser($user);
        $this->assertTrue($mock->check());
        $this->assertFalse($mock->guest());
    }

    public function testUserMethodReturnsCachedUser()
    {
        $user = m::mock(\Xpressengine\User\Authenticatable::class);
        $mock = $this->getGuard();
        $mock->setUser($user);
        $this->assertEquals($user, $mock->user());
    }
    public function testNullIsReturnedForUserIfNoUserFound()
    {
        $mock = $this->getGuard();
        $mock->getSession()->shouldReceive('get')->once()->andReturn(null);
        $this->assertInstanceOf(\Xpressengine\User\Models\Guest::class, $mock->user());
    }
    public function testUserIsSetToRetrievedUser()
    {
        $mock = $this->getGuard();
        $mock->getSession()->shouldReceive('get')->once()->andReturn(1);
        $user = m::mock(\Xpressengine\User\Authenticatable::class);
        $mock->getProvider()->shouldReceive('retrieveById')->once()->with(1)->andReturn($user);
        $this->assertEquals($user, $mock->user());
        $this->assertEquals($user, $mock->getUser());
    }

    public function testIdIfUserGiven()
    {
        $user = m::mock(\Xpressengine\User\Authenticatable::class);
        $user->shouldReceive('getAuthIdentifier')->once()->andReturn(1);

        $mock = $this->getGuard();
        $mock->getSession()->shouldReceive('get')->once()->andReturn(null);
        $mock->setUser($user);

        $this->assertEquals(1, $mock->id());
    }

    public function testIdReturnNullIfUserNotGiven()
    {
        $mock = $this->getGuard();
        $mock->getSession()->shouldReceive('get')->twice()->andReturn(null);

        $this->assertEquals(null, $mock->id());
    }

    public function testLogoutRemovesSessionTokenAndRememberMeCookie()
    {
        list($session, $provider, $request, $cookie) = $this->getMocks();
        $mock = $this->getMock(Guard::class, ['refreshRememberToken', 'clearUserDataFromStorage'], [$provider, $session, $request]);
        $mock->setCookieJar($cookies = m::mock(CookieJar::class));

        $user = m::mock(\Illuminate\Contracts\Auth\Authenticatable::class);

        $mock->expects($this->once())->method('clearUserDataFromStorage')->will($this->returnValue(null));
        $mock->expects($this->once())->method('refreshRememberToken')->with($user)->will($this->returnValue(null));

        $mock->setUser($user);
        $mock->logout();

        $this->assertInstanceOf(Guest::class, $mock->getUser());
    }

    protected function getGuard()
    {
        list($session, $provider, $request, $cookie) = $this->getMocks();
        return new Guard($provider, $session, $request);
    }

    protected function getMocks()
    {
        return [
            m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class),
            m::mock(\Illuminate\Contracts\Auth\UserProvider::class),
            Request::create('/', 'GET'),
            m::mock(\Illuminate\Cookie\CookieJar::class),
        ];
    }
}
