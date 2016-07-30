<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Tests\Template\Extension;

use Fusio\Impl\Context;
use Fusio\Impl\Request;
use Fusio\Impl\Template\Extension as func;
use Fusio\Impl\Template\Extension\Text;
use Fusio\Impl\Template\Factory;
use Fusio\Impl\Tests\ActionTestCaseTrait;
use PSX\Record\Record;

/**
 * TextTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class TextTest extends \PHPUnit_Framework_TestCase
{
    use ActionTestCaseTrait;

    protected function setUp()
    {
        // setup the extension so that we include the file and thus load the
        // filter functions
        new Text();
    }

    /**
     * @dataProvider templateDataProvider
     */
    public function testTemplate(array $parameters, $body, $expect)
    {
        $factory = new Factory(true);
        $parser  = $factory->newTextParser('foo');

        $request = $this->getRequest('GET', [], $parameters, [], $body);
        $context = $this->getContext();

        $template = <<<JSON
{
  "integer": {{ request.getParameter("integer")|integer(0, 3, 8) }},
  "string": {{ request.getParameter("string")|string("") }},
  "number": {{ request.getParameter("number")|number(0.4, 3, 8) }},
  "boolean": {{ request.getParameter("boolean")|boolean }},
  "array": {{ request.getParameter("array")|array }},
  "object": {{ request.getParameter("object")|object }},
  "body": {{ request.getBody()|object }}
}
JSON;

        $actual = $parser->parse($request, $context, $template);


        $this->assertJsonStringEqualsJsonString($expect, $actual);
    }

    public function templateDataProvider()
    {
        return [
            [[], null, '{
  "integer": 0,
  "string": "",
  "number": 0.4,
  "boolean": false,
  "array": [],
  "object": {},
  "body": {}
}'],
            [['integer' => 3, 'string' => 'foo', 'number' => 3.8, 'boolean' => true, 'array' => 'foo,bar', 'object' => 'foo'], Record::fromArray(['foo' => 'bar']), '{
  "integer": 3,
  "string": "foo",
  "number": 3.8,
  "boolean": true,
  "array": ["foo", "bar"],
  "object": {},
  "body": {
    "foo": "bar"
  }
}'],
            [['integer' => '3', 'string' => 'foo', 'number' => '3.8', 'boolean' => 'true', 'array' => 'foo,bar', 'object' => 'foo'], Record::fromArray(['foo' => 'bar']), '{
  "integer": 3,
  "string": "foo",
  "number": 3.8,
  "boolean": true,
  "array": ["foo", "bar"],
  "object": {},
  "body": {
    "foo": "bar"
  }
}'],
            [['integer' => '12', 'string' => 'foo', 'number' => '12.3', 'boolean' => 'foo', 'array' => 'foo', 'object' => 'foo'], Record::fromArray(['foo' => 'bar']), '{
  "integer": 0,
  "string": "foo",
  "number": 0.4,
  "boolean": true,
  "array": ["foo"],
  "object": {},
  "body": {
    "foo": "bar"
  }
}'],
            [['integer' => '1', 'string' => 'foo', 'number' => '2.3', 'boolean' => 'foo', 'array' => 'foo', 'object' => 'foo'], Record::fromArray(['foo' => ['bar' => 'baz']]), '{
  "integer": 0,
  "string": "foo",
  "number": 0.4,
  "boolean": true,
  "array": ["foo"],
  "object": {},
  "body": {
    "foo": {
      "bar": "baz"
    }
  }
}'],
        ];
    }

    public function testStringFilter()
    {
        $this->assertJsonStringEqualsJsonString('"foo"', func\fusio_string_filter('foo'));
        $this->assertJsonStringEqualsJsonString('""', func\fusio_string_filter(''));
        $this->assertJsonStringEqualsJsonString('""', func\fusio_string_filter(null));
    }

    public function testNumberFilter()
    {
        $this->assertJsonStringEqualsJsonString('1', func\fusio_number_filter('1'));
        $this->assertJsonStringEqualsJsonString('1.4', func\fusio_number_filter('1.4'));
        $this->assertJsonStringEqualsJsonString('0', func\fusio_number_filter('foo'));
        $this->assertJsonStringEqualsJsonString('0', func\fusio_number_filter(''));
        $this->assertJsonStringEqualsJsonString('0', func\fusio_number_filter(null));
    }

    public function testObjectFilter()
    {
        $this->assertJsonStringEqualsJsonString('{"foo": "bar"}', func\fusio_object_filter(['foo' => 'bar']));
        $this->assertJsonStringEqualsJsonString('{}', func\fusio_object_filter(null));
    }

    public function testArrayFilter()
    {
        $this->assertJsonStringEqualsJsonString('["bar"]', func\fusio_array_filter(['foo' => 'bar']));
        $this->assertJsonStringEqualsJsonString('["foo", "bar"]', func\fusio_array_filter('foo,bar'));
        $this->assertJsonStringEqualsJsonString('[]', func\fusio_array_filter(null));
    }

    public function testBooleanFilter()
    {
        $this->assertJsonStringEqualsJsonString('true', func\fusio_boolean_filter('1'));
        $this->assertJsonStringEqualsJsonString('false', func\fusio_boolean_filter('0'));
        $this->assertJsonStringEqualsJsonString('false', func\fusio_boolean_filter(null));
    }

    public function testIntegerFilter()
    {
        $this->assertJsonStringEqualsJsonString('1', func\fusio_integer_filter('1'));
        $this->assertJsonStringEqualsJsonString('1', func\fusio_integer_filter('1.4'));
        $this->assertJsonStringEqualsJsonString('0', func\fusio_integer_filter('foo'));
        $this->assertJsonStringEqualsJsonString('0', func\fusio_integer_filter(''));
        $this->assertJsonStringEqualsJsonString('0', func\fusio_integer_filter(null));
    }
}
