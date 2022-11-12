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

namespace Fusio\Impl\Tests\Service\Marketplace;

use Fusio\Impl\Service\Marketplace\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * AppTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AppTest extends TestCase
{
    public function testRemoteApp()
    {
        $remote = <<<YAML
version: 1.0.5
downloadUrl: 'https://github.com/apioo/fusio-apps-swaggerui/archive/v1.0.5.zip'
sha1Hash: 2107ad7121dfeaf4725cb64f84dc0630dad24ff0
description: 'The Swagger UI app renders a documentation based on the OpenAPI specification.'
screenshot: 'https://www.fusio-project.org/media/apps/swagger-ui.png'
website: 'https://github.com/apioo/fusio-apps-swaggerui'
YAML;

        $app = App::fromArray('swagger-ui', Yaml::parse($remote));

        $this->assertSame('swagger-ui', $app->getName());
        $this->assertSame('1.0.5', $app->getVersion());
        $this->assertSame('https://github.com/apioo/fusio-apps-swaggerui/archive/v1.0.5.zip', $app->getDownloadUrl());
        $this->assertSame('2107ad7121dfeaf4725cb64f84dc0630dad24ff0', $app->getSha1Hash());
        $this->assertSame('The Swagger UI app renders a documentation based on the OpenAPI specification.', $app->getDescription());
        $this->assertSame('https://www.fusio-project.org/media/apps/swagger-ui.png', $app->getScreenshot());
        $this->assertSame('https://github.com/apioo/fusio-apps-swaggerui', $app->getWebsite());
    }

    public function testLocalApp()
    {
        $local = <<<YAML
description: 'The Swagger UI app renders a documentation based on the OpenAPI specification.'
screenshot: 'https://www.fusio-project.org/media/apps/swagger-ui.png'
website: 'https://github.com/apioo/fusio-apps-swaggerui'
YAML;

        $app = App::fromArray('swagger-ui', Yaml::parse($local));

        $this->assertSame('swagger-ui', $app->getName());
        $this->assertSame('0.0.0', $app->getVersion());
        $this->assertSame(null, $app->getDownloadUrl());
        $this->assertSame(null, $app->getSha1Hash());
        $this->assertSame('The Swagger UI app renders a documentation based on the OpenAPI specification.', $app->getDescription());
        $this->assertSame('https://www.fusio-project.org/media/apps/swagger-ui.png', $app->getScreenshot());
        $this->assertSame('https://github.com/apioo/fusio-apps-swaggerui', $app->getWebsite());
    }
}
