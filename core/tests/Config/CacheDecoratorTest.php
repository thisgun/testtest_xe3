<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Tests\Config;

use Mockery as m;
use Xpressengine\Config\Repositories\CacheDecorator;

class CacheDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }
    
    public function testFind()
    {
        list($repo, $cache) = $this->getMocks();
        $instance = $this->getMock(CacheDecorator::class, ['getData'], [$repo, $cache]);

        $mockItem1 = new \stdClass;
        $mockItem1->name = 'foo';
        $mockItem2 = m::mock('Xpressengine\Config\ConfigEntity');
        $mockItem2->name = 'foo.bar';
        $instance->expects($this->at(0))->method('getData')->with('default', 'foo')->willReturn([
            $mockItem1, $mockItem2
        ]);

        $mockItem3 = new \stdClass;
        $mockItem3->name = 'baz';
        $instance->expects($this->at(1))->method('getData')->with('default', 'baz')->willReturn([
            $mockItem3
        ]);

        $config = $instance->find('default', 'foo.bar');
        $this->assertInstanceOf('Xpressengine\Config\ConfigEntity', $config);

        $config = $instance->find('default', 'baz.qux');
        $this->assertNull($config);
    }

    public function testFetchAncestor()
    {
        list($repo, $cache) = $this->getMocks();
        $instance = $this->getMock(CacheDecorator::class, ['getData'], [$repo, $cache]);

        $mockItem1 = new \stdClass;
        $mockItem1->name = 'foo';
        $mockItem2 = m::mock('Xpressengine\Config\ConfigEntity');
        $mockItem2->name = 'foo.bar';
        $instance->expects($this->once())->method('getData')->with('default', 'foo')->willReturn([
            $mockItem1, $mockItem2
        ]);

        $ancestor = $instance->fetchAncestor('default', 'foo.bar');
        $this->assertInstanceOf('stdClass', $ancestor[0]);
    }

    public function testFetchDescendant()
    {
        list($repo, $cache) = $this->getMocks();
        $instance = $this->getMock(CacheDecorator::class, ['getData'], [$repo, $cache]);

        $mockItem1 = m::mock('Xpressengine\Config\ConfigEntity');
        $mockItem1->name = 'foo';
        $mockItem2 = m::mock('Xpressengine\Config\ConfigEntity');
        $mockItem2->name = 'foo.bar';
        $mockItem3 = m::mock('Xpressengine\Config\ConfigEntity');
        $mockItem3->name = 'foo.bar.baz';
        $mockItem4 = m::mock('Xpressengine\Config\ConfigEntity');
        $mockItem4->name = 'foo.bar.qux';
        $instance->expects($this->exactly(2))->method('getData')->with('default', 'foo')->willReturn([
            $mockItem1, $mockItem2, $mockItem3, $mockItem4
        ]);

        $descendant = $instance->fetchDescendant('default', 'foo.bar');
        $this->assertEquals(2, count($descendant));

        $descendant = $instance->fetchDescendant('default', 'foo');
        $this->assertEquals(3, count($descendant));
    }
    
    private function getMocks()
    {
        return [
            m::mock('Xpressengine\Config\ConfigRepository'),
            m::mock('Xpressengine\Support\CacheInterface'),
        ];
    }
}
