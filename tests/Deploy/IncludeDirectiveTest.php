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

namespace Fusio\Impl\Tests\Deploy;

use Fusio\Impl\Deploy\EnvProperties;
use Fusio\Impl\Deploy\IncludeDirective;
use PHPUnit\Framework\TestCase;
use PSX\Framework\Config\Config;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * IncludeDirectiveTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class IncludeDirectiveTest extends TestCase
{
    public function testResolveTaggedValue()
    {
        $include = $this->newIncludeDirective();
        $data = $include->resolve(new TaggedValue('include', 'Resource/test.yaml'), __DIR__, '');

        $this->assertEquals('my_tag', $data['foo']['bar']->getTag());
        $this->assertEquals('test', $data['foo']['bar']->getValue());
    }

    public function testResolveTaggedValuePointer()
    {
        $include = $this->newIncludeDirective();
        $data = $include->resolve(new TaggedValue('include', 'Resource/test.yaml#/foo'), __DIR__, '');

        $this->assertEquals('my_tag', $data['bar']->getTag());
        $this->assertEquals('test', $data['bar']->getValue());
    }

    public function testResolveTaggedValueInvalidFile()
    {
        $this->expectException(\RuntimeException::class);

        $include = $this->newIncludeDirective();
        $include->resolve(new TaggedValue('include', 'Resource/foo.yaml'), __DIR__, '');
    }

    public function testResolveTaggedValueInvalidTag()
    {
        $this->expectException(\RuntimeException::class);

        $include = $this->newIncludeDirective();
        $include->resolve(new TaggedValue('foo', 'Resource/test.yaml'), __DIR__, '');
    }

    public function testResolveInvalidValue()
    {
        $this->expectException(\RuntimeException::class);

        $include = $this->newIncludeDirective();
        $include->resolve('foo', __DIR__, '');
    }

    public function testResolveArray()
    {
        $include = $this->newIncludeDirective();
        $data = $include->resolve(['foo' => 'bar'], __DIR__, '');

        $this->assertEquals(['foo' => 'bar'], $data);
    }
    
    private function newIncludeDirective()
    {
        return new IncludeDirective(new EnvProperties(new Config([])));
    }
}
