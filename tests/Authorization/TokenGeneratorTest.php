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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Authorization\TokenGenerator;

/**
 * TokenGeneratorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class TokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateToken()
    {
        $this->assertEquals(80, strlen(TokenGenerator::generateToken()));
    }

    public function testGenerateCode()
    {
        $this->assertEquals(16, strlen(TokenGenerator::generateCode()));
    }

    public function testGenerateAppKey()
    {
        $this->assertEquals(36, strlen(TokenGenerator::generateAppKey()));
    }

    public function testGenerateAppSecret()
    {
        $this->assertEquals(64, strlen(TokenGenerator::generateAppSecret()));
    }

    public function testGenerateUserPassword()
    {
        $this->assertEquals(20, strlen(TokenGenerator::generateUserPassword()));
    }
}
