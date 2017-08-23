<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Tests\Factory;

use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Factory\EngineDetector;
use Fusio\Impl\Factory\Resolver;

/**
 * EngineDetectorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
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
