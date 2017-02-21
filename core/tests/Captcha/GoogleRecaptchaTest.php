<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\Captcha;

use Mockery as m;
use Xpressengine\Captcha\Services\GoogleRecaptcha as Origin;

class GoogleRecaptchaTest extends \PHPUnit_Framework_TestCase
{
    public static $captcha;

    private static function setCaptcha()
    {
        static::$captcha = m::mock('ReCaptcha\ReCaptcha');
    }

    private static function unsetCaptcha()
    {
        static::$captcha = null;
    }

    public function setUp()
    {
        static::setCaptcha();
    }

    public function tearDown()
    {
        static::unsetCaptcha();
        m::close();
    }

    public function testVerify()
    {
        list($siteKey, $secret, $request, $frontend) = $this->getMocks();
        $instance = $this->createInstance($siteKey, $secret, $request, $frontend);

        $request->shouldReceive('get')->once()->andReturn('val');
        $request->shouldReceive('ip')->once()->andReturn('0.0.0.0');

        $mockResponse = m::mock('ReCaptcha\Response');
        $mockResponse->shouldReceive('isSuccess')->andReturn(true);
        static::$captcha->shouldReceive('verify')->once()->with('val', '0.0.0.0')->andReturn($mockResponse);

        $instance->verify();
    }

    public function testRender()
    {
        list($siteKey, $secret, $request, $frontend) = $this->getMocks();
        $instance = $this->createInstance($siteKey, $secret, $request, $frontend);

        $frontend->shouldReceive('js')->once()->andReturnSelf();
        $frontend->shouldReceive('appendTo')->once()->andReturnSelf();
        $frontend->shouldReceive('load')->once();

        $instance->render();
    }

    private function getMocks()
    {
        return [
            'siteKey',
            'secret',
            m::mock('Illuminate\Http\Request'),
            m::mock('Xpressengine\Presenter\Html\FrontendHandler'),
            m::mock('ReCaptcha\ReCaptcha')
        ];
    }

    private function createInstance($siteKey, $secret, $request, $frontend)
    {
        return new GoogleRecaptcha($siteKey, $secret, $request, $frontend);
    }
}


class GoogleRecaptcha extends Origin
{
    protected function create()
    {
        return $this->captcha = GoogleRecaptchaTest::$captcha;
    }
}
