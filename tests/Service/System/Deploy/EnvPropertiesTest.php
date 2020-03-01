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

namespace Fusio\Impl\Tests\Service\System\Deploy;

use Fusio\Impl\Service\System\Deploy\EnvProperties;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * EnvPropertiesTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class EnvPropertiesTest extends TestCase
{
    public function testReplace()
    {
        $data   = 'dbname: "${env.FOO}"';
        $actual = EnvProperties::replace($data, [
            'FOO' => 'bar'
        ]);
        $expect = 'dbname: "bar"';

        $this->assertEquals($expect, $actual, $actual);
    }

    public function testReplaceMultiple()
    {
        $data = <<<'YAML'
Default-Connection:
  class: Fusio\Adapter\Sql\Connection\Sql
  config:
    dbname: "${env.APIOO_DB_NAME}"
    user: "${env.APIOO_DB_USER}"
    password: "${env.APIOO_DB_PW}"
    host: "${env.MYSQL_HOST}"
    driver: "pdo_mysql"

YAML;

        $actual = EnvProperties::replace($data, [
            'APIOO_DB_NAME' => 'db_name',
            'APIOO_DB_USER' => 'db_user',
            'APIOO_DB_PW'   => 'db_pw',
            'MYSQL_HOST'    => 'host',
        ]);
        $data   = Yaml::parse($actual);
        $config = $data['Default-Connection']['config'];

        $this->assertEquals('db_name', $config['dbname']);
        $this->assertEquals('db_user', $config['user']);
        $this->assertEquals('db_pw', $config['password']);
        $this->assertEquals('host', $config['host']);
    }

    public function testReplaceCase()
    {
        $data   = 'dbname: "${env.FOO}"';
        $actual = EnvProperties::replace($data, [
            'foo' => 'bar'
        ]);
        $expect = 'dbname: "bar"';

        $this->assertEquals($expect, $actual, $actual);
    }

    public function testReplaceEscape()
    {
        $data   = 'dbname: "${env.FOO}"';
        $actual = EnvProperties::replace($data, [
            'foo' => 'foo' . "\n" . 'bar"test'
        ]);
        $expect = 'dbname: "foo\nbar\"test"';

        $this->assertEquals($expect, $actual, $actual);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReplaceUnknownType()
    {
        EnvProperties::replace('dbname: "${foo.FOO}"', []);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReplaceUnknownKey()
    {
        EnvProperties::replace('dbname: "${env.FOO}"', [
            'baz' => 'bar'
        ]);
    }
}
