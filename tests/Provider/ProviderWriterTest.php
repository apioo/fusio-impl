<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Provider\ProviderLoader;
use Fusio\Impl\Provider\ProviderWriter;
use Fusio\Impl\Tests\DbTestCase;

/**
 * ProviderWriterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ProviderWriterTest extends DbTestCase
{
    public function testWrite()
    {
        $writer = new ProviderWriter($this->connection);
        $count  = $writer->write([
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

        $this->assertSame(4, $count);

        $actual = $this->connection->fetchAll('SELECT type, class FROM fusio_provider ORDER BY class ASC');
        $expect = [
            [
                'type' => 'action',
                'class' => 'stdClass',
            ],
            [
                'type' => 'connection',
                'class' => 'stdClass',
            ],
            [
                'type' => 'payment',
                'class' => 'stdClass',
            ],
            [
                'type' => 'user',
                'class' => 'stdClass',
            ]
        ];

        $this->assertEquals($expect, $actual);
    }

    public function testWriteEmpty()
    {
        $writer = new ProviderWriter($this->connection);
        $count  = $writer->write([]);

        $this->assertSame(0, $count);
    }

    public function testWriteInvalidClass()
    {
        $writer = new ProviderWriter($this->connection);
        $count  = $writer->write([
            'action' => [
                'Foo\Bar\Baz',
            ],
        ]);

        $this->assertSame(0, $count);
    }
}
