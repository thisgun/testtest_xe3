<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\Routing;

use Mockery as m;
use Xpressengine\Routing\Repositories\DatabaseRouteRepository;

class DatabaseRouteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['createModel'], [$configs, $model]);

        $mockModel = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->andReturnSelf();
        $mockModel->shouldReceive('get')->once()->andReturn(['route1', 'route2']);

        $instance->expects($this->once())->method('createModel')->willReturn($mockModel);

        $routes = $instance->all();

        $this->assertEquals(2, count($routes));
    }

    public function testFindByUrlAndSiteKey()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['createModel'], [$configs, $model]);

        $mockModel = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->andReturnSelf();
        $mockModel->shouldReceive('where')->once()->with('url', 'board')->andReturnSelf();
        $mockModel->shouldReceive('where')->once()->with('siteKey', 'default')->andReturnSelf();
        $mockModel->shouldReceive('first')->once()->andReturn('route');

        $instance->expects($this->once())->method('createModel')->willReturn($mockModel);


        $route = $instance->findByUrlAndSiteKey('board', 'default');

        $this->assertEquals('route', $route);
    }

    public function testFindByInstanceId()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['createModel'], [$configs, $model]);

        $mockModel = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->andReturnSelf();
        $mockModel->shouldReceive('where')->once()->with('instanceId', 'abcdefg')->andReturnSelf();
        $mockModel->shouldReceive('first')->once()->andReturn('route');

        $instance->expects($this->once())->method('createModel')->willReturn($mockModel);


        $route = $instance->findByInstanceId('abcdefg');

        $this->assertEquals('route', $route);
    }

    public function testFetchBySiteKey()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['createModel'], [$configs, $model]);

        $mockModel = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->andReturnSelf();
        $mockModel->shouldReceive('where')->once()->with('siteKey', 'default')->andReturnSelf();
        $mockModel->shouldReceive('get')->once()->andReturn(['route1', 'route2']);

        $instance->expects($this->once())->method('createModel')->willReturn($mockModel);


        $routes = $instance->fetchBySiteKey('default');

        $this->assertEquals(2, count($routes));
    }

    public function testFetchByModule()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['createModel'], [$configs, $model]);

        $mockModel = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->andReturnSelf();
        $mockModel->shouldReceive('where')->once()->with('module', 'module/xe@some')->andReturnSelf();
        $mockModel->shouldReceive('get')->once()->andReturn(['route1', 'route2']);

        $instance->expects($this->once())->method('createModel')->willReturn($mockModel);


        $routes = $instance->fetchByModule('module/xe@some');

        $this->assertEquals(2, count($routes));
    }

    public function testPut()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['validateUrl'], [$configs, $model]);

        $mockRoute = m::mock('Xpressengine\Routing\InstanceRoute');
        $mockRoute->shouldReceive('getAttribute')->with('siteKey')->andReturn('default');
        $mockRoute->shouldReceive('getAttribute')->with('url')->andReturn('board');
        $mockRoute->shouldReceive('getAttribute')->with('exists')->andReturn(false);
        $mockRoute->shouldReceive('save')->once();

        $instance->expects($this->once())->method('validateUrl')->willReturn(true);

        $instance->put($mockRoute);
    }

    public function testPutThrowsExceptionWhenUnusableRouteGiven()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['validateUrl'], [$configs, $model]);

        $mockRoute = m::mock('Xpressengine\Routing\InstanceRoute');
        $mockRoute->shouldReceive('getAttribute')->with('siteKey')->andReturn('default');
        $mockRoute->shouldReceive('getAttribute')->with('url')->andReturn('board');
        $mockRoute->shouldReceive('getAttribute')->with('exists')->andReturn(false);

        $instance->expects($this->once())->method('validateUrl')->willReturn(false);

        try {
            $instance->put($mockRoute);

            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Xpressengine\Routing\Exceptions\UnusableUrlException', $e);
        }
    }

    public function testValidateUrl()
    {
        list($configs, $model) = $this->getMocks();
        $instance = $this->getMock(DatabaseRouteRepository::class, ['findByUrlAndSiteKey'], [$configs, $model]);

        $configs->shouldReceive('get')->once()->with('xe.routing')->andReturn([
            'settingsPrefix' => 'settings',
            'fixedPrefix' => 'plugin'
        ]);
        
        $instance->expects($this->once())->method('findByUrlAndSiteKey')->with('board', 'default')->willReturn(null);


        $result = $this->invokedMethod($instance, 'validateUrl', ['default', 'board', true]);

        $this->assertTrue($result);
    }

    private function getMocks()
    {
        return [
            m::mock('Illuminate\Contracts\Config\Repository'),
            'Xpressengine\Routing\InstanceRoute'
        ];
    }

    private function invokedMethod($object, $methodName, $parameters = [])
    {
        $rfc = new \ReflectionClass($object);
        $method = $rfc->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
