<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Record;

use Fusio\Impl\Record\PassthruRecord;
use PHPUnit\Framework\TestCase;
use PSX\Record\Record;
use PSX\Record\RecordInterface;

/**
 * PassthruRecordTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class PassthruRecordTest extends TestCase
{
    public function testGet()
    {
        $object = Record::fromArray(['foo' => 'bar']);
        $record = new PassthruRecord($object);

        $this->assertEquals('bar', $record->getProperty('foo'));
        $this->assertEquals('bar', $record['foo']);
        $this->assertInstanceOf(RecordInterface::class, $record->getPayload());
    }

    public function testGetNested()
    {
        $object = Record::fromArray(['foo' => (object) ['foo' => 'bar']]);
        $record = new PassthruRecord($object);

        $this->assertEquals('bar', $record->getProperty('foo.foo'));
        $this->assertEquals('bar', $record['foo.foo']);
        $this->assertInstanceOf(RecordInterface::class, $record->getPayload());
    }

    public function testGetArray()
    {
        $object = Record::fromArray(['foo' => ['foo', 'bar']]);
        $record = new PassthruRecord($object);

        $this->assertEquals('bar', $record->getProperty('foo[1]'));
        $this->assertEquals('bar', $record['foo[1]']);
        $this->assertInstanceOf(RecordInterface::class, $record->getPayload());
    }
}
