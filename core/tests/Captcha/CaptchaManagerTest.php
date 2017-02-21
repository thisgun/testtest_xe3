<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\Captcha;

use Mockery as m;
use Xpressengine\Captcha\CaptchaManager;

class CaptchaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testCreateGoogleDriver()
    {
        list($app, $config) = $this->getMocks();
        $instance = new CaptchaManager($app);

        $mockReq = m::mock('Illuminate\Http\Request');
        $mockFE = m::mock('Xpressengine\Presenter\Html\FrontendHandler');

        $app->shouldReceive('offsetGet')->with('config')->andReturn($config);
        $app->shouldReceive('offsetGet')->with('request')->andReturn($mockReq);
        $app->shouldReceive('offsetGet')->with('xe.frontend')->andReturn($mockFE);

        $driver = $instance->createGoogleDriver();

        $this->assertInstanceOf('Xpressengine\Captcha\Services\GoogleRecaptcha', $driver);
    }

    public function testGetAndSetDefaultDriver()
    {
        list($app, $config) = $this->getMocks();
        $instance = new CaptchaManager($app);

        $app->shouldReceive('offsetGet')->with('config')->andReturn($config);

//        $app->shouldReceive('offsetSet');

        $this->assertEquals('google', $instance->getDefaultDriver());

        $instance->setDefaultDriver('another');

        $this->assertEquals('another', $instance->getDefaultDriver());
    }

    private function getMocks()
    {
        return [
            m::mock('Illuminate\Foundation\Application'),
            new DummyConfig([
                'captcha.driver' => 'google',
                'captcha.apis' => [
                    'google' => [
                        'siteKey' => 'site_key',
                        'secret' => 'secret_str'
                    ],
                    'another' => [
                        'siteKey' => 'another_site_key',
                        'secret' => 'another_secret_str'
                    ]
                ]
            ])
        ];
    }
}

class DummyConfig implements \ArrayAccess
{
    private $arr;

    public function __construct($arr)
    {
        $this->arr = $arr;
    }

    public function offsetGet($name)
    {
        return $this->arr[$name];
    }

    public function offsetSet($name, $value)
    {
        $this->arr[$name] = $value;
    }

    public function offsetUnset($name)
    {
        unset($this->arr[$name]);
    }

    public function offsetExists($name)
    {
        return isset($this->arr[$name]);
    }
}
