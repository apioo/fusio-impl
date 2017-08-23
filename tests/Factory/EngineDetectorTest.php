<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Factory;

use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Factory\EngineDetector;
use Fusio\Impl\Factory\Resolver;

/**
 * EngineDetectorTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class EngineDetectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderGetEngine
     */
    public function testGetEngine($class, $expectClass, $expectEngine)
    {
        $engine = EngineDetector::getEngine($class);
        
        $this->assertEquals($expectClass, $class);
        $this->assertEquals($expectEngine, $engine);
    }
    
    public function dataProviderGetEngine()
    {
        return [
            [self::class, self::class, PhpClass::class],

            [__DIR__ . '/resources/file.php', __DIR__ . '/resources/file.php', Resolver\PhpFile::class],
            [__DIR__ . '/resources/file.js', __DIR__ . '/resources/file.js', Resolver\JavascriptFile::class],
            [__DIR__ . '/resources/file.foo', __DIR__ . '/resources/file.foo', PhpClass::class],

            ['file://' . __DIR__ . '/resources/file.php', __DIR__ . '/resources/file.php', Resolver\PhpFile::class],
            ['file://' . __DIR__ . '/resources/file.js', __DIR__ . '/resources/file.js', Resolver\JavascriptFile::class],
            ['file://' . __DIR__ . '/resources/file.foo', __DIR__ . '/resources/file.foo', PhpClass::class],

            ['file+php://' . __DIR__ . '/resources/file.php', __DIR__ . '/resources/file.php', Resolver\PhpFile::class],
            ['file+php://' . __DIR__ . '/resources/file.js', __DIR__ . '/resources/file.js', Resolver\PhpFile::class],
            ['file+php://' . __DIR__ . '/resources/file.foo', __DIR__ . '/resources/file.foo', Resolver\PhpFile::class],

            ['file+js://' . __DIR__ . '/resources/file.php', __DIR__ . '/resources/file.php', Resolver\JavascriptFile::class],
            ['file+js://' . __DIR__ . '/resources/file.js', __DIR__ . '/resources/file.js', Resolver\JavascriptFile::class],
            ['file+js://' . __DIR__ . '/resources/file.foo', __DIR__ . '/resources/file.foo', Resolver\JavascriptFile::class],

            ['php://' . self::class, self::class, PhpClass::class],
            ['php://Foo\\Bar', 'Foo\\Bar', PhpClass::class],

            ['http://google.de', 'http://google.de', Resolver\HttpUrl::class],
            ['https://google.de', 'https://google.de', Resolver\HttpUrl::class],
        ];
    }
}
