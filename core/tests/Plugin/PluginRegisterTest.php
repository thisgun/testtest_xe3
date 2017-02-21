<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\Plugin;

use Mockery;
use Xpressengine\Plugin\ComponentInterface;
use Xpressengine\Plugin\PluginRegister;
use Xpressengine\Register\Container;

class PluginRegisterTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(PluginRegister::class, $this->getRegister());
    }

    public function testAdd()
    {
        $container = $this->makeContainer();
        $container->shouldReceive('set')
            ->with('module/pluginName@component', TestComponent::class)
            ->once()
            ->andReturnNull();
        $container->shouldReceive('set')
            ->with('module.module/pluginName@component', TestComponent::class)
            ->once()
            ->andReturnNull();
        $register = $this->getRegister($container);

        $register->add(TestComponent::class);
    }

    protected function makeContainer()
    {
        return Mockery::mock('\Xpressengine\Register\Container');
    }

    protected function getRegister(Container $container = null)
    {
        if ($container === null) {
            $container = $this->makeContainer();
        }

        return new PluginRegister($container);
    }
}


class TestComponent implements ComponentInterface
{
    protected static $id = 'module/pluginName@component';

    public static function getId()
    {
        return static::$id;
    }

    public static function setId($id)
    {
    }

    public static function setComponentInfo($key, $value = null)
    {
    }

    public static function getComponentInfo($value = null)
    {
    }

    public static function boot()
    {
    }

    public static function getSettingsURI()
    {
    }
}
