<?php
/**
 * @author    XE Developers <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link      https://xpressengine.io
 */

namespace Xpressengine\Presenter\Html\Tags {
    function asset($file)
    {
        return $file;
    }
}

namespace Xpressengine\Tests\Frontend {
    use \PHPUnit_Framework_TestCase;
    use Xpressengine\Presenter\Html\FrontendHandler;

    class FrontendHandlerTest extends PHPUnit_Framework_TestCase
    {

        protected function tearDown()
        {
            \Mockery::close();
            parent::tearDown();
        }

        public function testConstruct()
        {
            $frontend = new FrontendHandler();
        }

        public function testConstructWithTagArray()
        {
            $tags     = $this->getTagList();
            $frontend = new FrontendHandler($tags);

            $refl = new \ReflectionObject($frontend);
            $reflTags = $refl->getProperty('tags');
            $reflTags->setAccessible(true);

            $this->assertEquals($tags, $reflTags->getValue($frontend));
            return $frontend;
        }

        public function testAddTag()
        {
            $frontend = new FrontendHandler();

            $tags = $this->getTagList();

            $frontend->addTag('js', '\Xpressengine\Tests\Frontend\TagStub');

            $refl = new \ReflectionObject($frontend);
            $reflTags = $refl->getProperty('tags');
            $reflTags->setAccessible(true);

            $this->assertEquals($tags, $reflTags->getValue($frontend));
        }

        public function testAddTagWithArray()
        {
            $frontend = new FrontendHandler();

            $tags = $this->getTagList();

            $frontend->addTag($tags);

            $refl = new \ReflectionObject($frontend);
            $reflTags = $refl->getProperty('tags');
            $reflTags->setAccessible(true);

            $this->assertEquals($tags, $reflTags->getValue($frontend));

        }

        /**
         * @depends testConstructWithTagArray
         */
        public function testMagicMethodForJSFile(FrontendHandler $frontend)
        {
            \Xpressengine\Presenter\Html\Tags\JSFile::init();

            $js = $frontend->js('path/to/file.js');
            $this->assertInstanceOf('\Xpressengine\Tests\Frontend\TagStub', $js);
        }

        public function testOutput()
        {
            $tags     = $this->getTagList();
            $frontend = new FrontendHandler($tags);

            $output = $frontend->output('js', 'body.prepend');
        }

        protected function setUp()
        {

            parent::setUp();
        }

        /**
         * getTagList
         *
         * @return array
         */
        protected function getTagList()
        {
            $tags = [
                'js' => '\Xpressengine\Tests\Frontend\TagStub'
            ];
            return $tags;
        }
    }

    class TagStub
    {
        private $file;

        public function __construct($file)
        {

            $this->file = $file;
        }

        public static function output()
        {
            return 'hi';
        }

        public static function init()
        {
        }
    }
}
