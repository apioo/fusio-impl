<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Attribute\Path;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception\PermanentRedirectException;
use PSX\Schema\ContentType;

/**
 * WellKnownController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class WellKnownController extends ControllerAbstract
{
    public function __construct(private readonly FrameworkConfig $frameworkConfig)
    {
    }

    #[Get]
    #[Path('/.well-known/oauth-authorization-server')]
    public function redirectOAuthAuthorizationServer(): mixed
    {
        throw new PermanentRedirectException($this->frameworkConfig->getDispatchUrl('system', 'oauth-authorization-server'));
    }

    #[Get]
    #[Path('/.well-known/api-catalog')]
    public function redirectAPICatalog(): mixed
    {
        throw new PermanentRedirectException($this->frameworkConfig->getDispatchUrl('system', 'api-catalog'));
    }

    #[Get]
    #[Path('/.well-known/security.txt')]
    #[Outgoing(200, ContentType::TEXT)]
    public function getSecurityTxt(): HttpResponse
    {
        $expires = (new \DateTime())->add(new \DateInterval('P1M'))->format('Y-m-d\T00:00:00.000\Z');

        $body = <<<TEXT
Contact: mailto:security@fusio-project.org
Contact: https://github.com/apioo/fusio
Contact: https://chrisk.app/
Expires: {$expires}
Encryption: https://chrisk.app/pub.key
Preferred-Languages: en

TEXT;

        return new HttpResponse(200, ['Content-Type' => 'text/plain'], $body);
    }
}
