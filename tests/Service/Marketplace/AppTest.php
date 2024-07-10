<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Service\Marketplace;

use Fusio\Impl\Dto\Marketplace\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * AppTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AppTest extends TestCase
{
    public function testApp()
    {
        $remote = <<<YAML
name: swagger-ui
version: 1.0.5
downloadUrl: 'https://github.com/apioo/fusio-apps-swaggerui/archive/v1.0.5.zip'
sha1Hash: 2107ad7121dfeaf4725cb64f84dc0630dad24ff0
description: 'The Swagger UI app renders a documentation based on the OpenAPI specification.'
screenshot: 'https://www.fusio-project.org/media/apps/swagger-ui.png'
website: 'https://github.com/apioo/fusio-apps-swaggerui'
YAML;

        $app = App::fromObject((object) Yaml::parse($remote));

        $this->assertSame('swagger-ui', $app->getName());
        $this->assertSame('1.0.5', $app->getVersion());
        $this->assertSame('https://github.com/apioo/fusio-apps-swaggerui/archive/v1.0.5.zip', $app->getDownloadUrl());
        $this->assertSame('2107ad7121dfeaf4725cb64f84dc0630dad24ff0', $app->getSha1Hash());
        $this->assertSame('The Swagger UI app renders a documentation based on the OpenAPI specification.', $app->getDescription());
        $this->assertSame('https://www.fusio-project.org/media/apps/swagger-ui.png', $app->getScreenshot());
        $this->assertSame('https://github.com/apioo/fusio-apps-swaggerui', $app->getWebsite());
    }
}
