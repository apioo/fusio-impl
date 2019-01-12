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

use Fusio\Impl\Provider\ProviderLoader;
use Fusio\Impl\Tests\DbTestCase;

/**
 * ProviderLoaderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ProviderLoaderTest extends DbTestCase
{
    public function testGetConfig()
    {
        $loader = new ProviderLoader($this->connection, __DIR__ . '/Resource/provider.php');
        $config = $loader->getConfig();

        $actual = $config->getArrayCopy();
        $expect = [
            'action' => [
                'fileprocessor' => \Fusio\Adapter\File\Action\FileProcessor::class,
                'httpprocessor' => \Fusio\Adapter\Http\Action\HttpProcessor::class,
                'phpprocessor' => \Fusio\Adapter\Php\Action\PhpProcessor::class,
                'phpsandbox' => \Fusio\Adapter\Php\Action\PhpSandbox::class,
                'sqltable' => \Fusio\Adapter\Sql\Action\SqlTable::class,
                'utilstaticresponse' => \Fusio\Adapter\Util\Action\UtilStaticResponse::class,
                'stdclass' => \stdClass::class,
            ],
            'connection' => [
                'http' => \Fusio\Adapter\Http\Connection\Http::class,
                'sql' => \Fusio\Adapter\Sql\Connection\Sql::class,
                'sqladvanced' => \Fusio\Adapter\Sql\Connection\SqlAdvanced::class,
                'stdclass' => \stdClass::class,
            ],
            'payment' => [
                'stdclass' => \stdClass::class,
            ],
            'user' => [
                'facebook' => \Fusio\Impl\Provider\User\Facebook::class,
                'github' => \Fusio\Impl\Provider\User\Github::class,
                'google' => \Fusio\Impl\Provider\User\Google::class,
                'stdclass' => \stdClass::class,
            ],
        ];

        $this->assertEquals($expect, $actual);
    }
}
