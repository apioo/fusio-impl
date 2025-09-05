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

use Fusio\Impl\Service\WellKnown\APICatalog;
use Fusio\Impl\Service\WellKnown\OAuthAuthorizationServer;
use Fusio\Impl\Service\WellKnown\OAuthProtectedResource;
use Fusio\Impl\Service\WellKnown\OpenIDConfiguration;
use Fusio\Impl\Service\WellKnown\SecurityTxt;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Attribute\Path;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Environment\HttpResponse;
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
    public function __construct(
        private readonly APICatalog $apiCatalog,
        private readonly OAuthAuthorizationServer $oauthAuthorizationServer,
        private readonly OAuthProtectedResource $oauthProtectedResource,
        private readonly OpenIDConfiguration $openIDConfiguration,
        private readonly SecurityTxt $securityTxt,
    ) {
    }

    #[Get]
    #[Path('/.well-known/api-catalog')]
    public function getAPICatalog(): HttpResponse
    {
        return new HttpResponse(200, [
            'Content-Type' => 'application/linkset+json; profile="https://www.rfc-editor.org/info/rfc9727"',
        ], [
            'linkset' => [
                $this->apiCatalog->buildLinkSet()
            ],
        ]);
    }

    #[Get]
    #[Path('/.well-known/oauth-authorization-server')]
    public function getOAuthAuthorizationServer(): mixed
    {
        return $this->oauthAuthorizationServer->get();
    }

    #[Get]
    #[Path('/.well-known/oauth-protected-resource')]
    public function getOAuthProtectedResource(): mixed
    {
        return $this->oauthProtectedResource->get();
    }

    #[Get]
    #[Path('/.well-known/oauth-protected-resource/*resource')]
    public function getOAuthProtectedResourceSpecific(string $resource): mixed
    {
        return $this->oauthProtectedResource->get($resource);
    }

    #[Get]
    #[Path('/.well-known/openid-configuration')]
    public function getOpenIDConfiguration(): mixed
    {
        return $this->openIDConfiguration->get();
    }

    #[Get]
    #[Path('/.well-known/security.txt')]
    #[Outgoing(200, ContentType::TEXT)]
    public function getSecurityTxt(): HttpResponse
    {
        return new HttpResponse(200, [
            'Content-Type' => 'text/plain'
        ],
            $this->securityTxt->build()
        );
    }
}
