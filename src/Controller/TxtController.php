<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Controller;

use Fusio\Impl\Service;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Attribute\Path;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Environment\HttpResponse;
use PSX\Schema\ContentType;

/**
 * TxtController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TxtController extends ControllerAbstract
{
    public function __construct(private readonly Service\Config $configService)
    {
    }

    #[Get]
    #[Path('/humans.txt')]
    #[Outgoing(200, ContentType::TEXT)]
    public function getHumansTxt(): HttpResponse
    {
        $title = $this->configService->getValue('info_title') ?: 'Fusio';
        $description = $this->configService->getValue('info_description') ?: null;

        $body = <<<TEXT

{$title}

{$description}

--

This API is powered by Fusio.
https://www.fusio-project.org/

TEXT;

        return new HttpResponse(200, ['Content-Type' => 'text/plain'], $body);
    }

    #[Get]
    #[Path('/robots.txt')]
    #[Outgoing(200, ContentType::TEXT)]
    public function getRobotsTxt(): HttpResponse
    {
        $body = <<<TEXT
User-agent: *
Disallow: /

TEXT;

        return new HttpResponse(200, ['Content-Type' => 'text/plain'], $body);
    }
}
