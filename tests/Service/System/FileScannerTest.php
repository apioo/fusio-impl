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

namespace Fusio\Impl\Tests\Service\System;

use Fusio\Impl\Service\System\FileScanner;
use PSX\Framework\Config\Config;

/**
 * FileScannerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class FileScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testScan()
    {
        $scanner = new FileScanner(new Config(['fusio_path_apps' => __DIR__ . '/apps', 'psx_url' => 'http://foo.bar']));
        $result  = $scanner->scan();

        $this->assertEquals('<b>Url: http://foo.bar</b>', file_get_contents(__DIR__ . '/apps/test-app/index.html'));
        $this->assertEquals(['[REPLACED] file Environment variables at index.html'], $result->getLogs());
    }
}
