<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Provider;

use Fusio\Impl\Provider\ProviderConfig;
use Fusio\Impl\Provider\ProviderWriter;

/**
 * ProviderWriterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ProviderWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $file   = __DIR__ . '/Resource/provider.php';
        $config = new ProviderConfig($this->getConfig());
        $writer = new ProviderWriter($config, $file);

        $bytes = $writer->write([
            'action' => [
                \stdClass::class
            ],
            'connection' => [
                \stdClass::class
            ],
            'payment' => [
                \stdClass::class
            ],
            'user' => [
                \stdClass::class
            ],
        ]);

        $this->assertNotEmpty($bytes);

        $actual = include $file;
        $expect = $this->getConfig();
        $expect['action'][] = \stdClass::class;
        $expect['connection'][] = \stdClass::class;
        $expect['payment'][] = \stdClass::class;
        $expect['user'][] = \stdClass::class;

        $this->assertEquals($expect, $actual);
    }

    public function testWriteNoFile()
    {
        $file   = __DIR__ . '/Resource/foo.php';
        $config = new ProviderConfig($this->getConfig());
        $writer = new ProviderWriter($config, $file);

        $bytes = $writer->write([
            'action' => [
                TestAction::class
            ],
        ]);

        $this->assertEmpty($bytes);
    }

    public function testWriteEmpty()
    {
        $file   = __DIR__ . '/Resource/provider.php';
        $config = new ProviderConfig($this->getConfig());
        $writer = new ProviderWriter($config, $file);

        $bytes = $writer->write([]);

        $this->assertEmpty($bytes);
    }

    public function testWriteNoChanges()
    {
        $file   = __DIR__ . '/Resource/provider.php';
        $config = new ProviderConfig($this->getConfig());
        $writer = new ProviderWriter($config, $file);

        $bytes = $writer->write([
            'action' => [
                \Fusio\Adapter\File\Action\FileProcessor::class,
            ],
        ]);

        $this->assertEmpty($bytes);
    }

    public function testWriteInvalidClass()
    {
        $file   = __DIR__ . '/Resource/provider.php';
        $config = new ProviderConfig($this->getConfig());
        $writer = new ProviderWriter($config, $file);

        $bytes = $writer->write([
            'action' => [
                'Foo\Bar\Baz',
            ],
        ]);

        $this->assertEmpty($bytes);
    }

    private function getConfig()
    {
        return [
            'action' => [
                \Fusio\Adapter\File\Action\FileProcessor::class,
                \Fusio\Adapter\Http\Action\HttpProcessor::class,
                \Fusio\Adapter\Php\Action\PhpProcessor::class,
                \Fusio\Adapter\Php\Action\PhpSandbox::class,
                \Fusio\Adapter\Sql\Action\SqlTable::class,
                \Fusio\Adapter\Util\Action\UtilStaticResponse::class,
                \Fusio\Adapter\V8\Action\V8Processor::class,
            ],
            'connection' => [
                \Fusio\Adapter\Http\Connection\Http::class,
                \Fusio\Adapter\Sql\Connection\Sql::class,
                \Fusio\Adapter\Sql\Connection\SqlAdvanced::class,
            ],
            'payment' => [
            ],
            'user' => [
                \Fusio\Impl\Provider\User\Facebook::class,
                \Fusio\Impl\Provider\User\Github::class,
                \Fusio\Impl\Provider\User\Google::class,
            ],
        ];
    }
}
