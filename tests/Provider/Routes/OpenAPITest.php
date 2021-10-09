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

namespace Fusio\Impl\Tests\Provider\Routes;

use Fusio\Engine\Parameters;
use Fusio\Engine\Routes\Setup;
use Fusio\Impl\Provider\Routes\OpenAPI;
use Fusio\Impl\Tests\DbTestCase;

/**
 * OpenAPITest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class OpenAPITest extends DbTestCase
{
    public function testSetupPetstore()
    {
        $spec  = file_get_contents(__DIR__ . '/resource/openapi_petstore.json');
        $setup = new Setup();

        (new OpenAPI())->setup($setup, '/', new Parameters(['spec' => $spec]));

        $this->assertEquals(3, count($setup->getActions()));
        $this->assertEquals(5, count($setup->getSchemas()));
        $this->assertEquals(2, count($setup->getRoutes()));
    }
}
