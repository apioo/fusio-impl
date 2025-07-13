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

use Fusio\Impl\Consumer\View;
use Fusio\Impl\Service;
use Fusio\Model\Consumer\AuthorizeRequest;
use PSX\Api\Attribute\Body;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Path;
use PSX\Api\Attribute\Post;
use PSX\Api\Attribute\Query;
use PSX\Data\Body\Form;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Http\Writer\Template;
use PSX\Framework\Loader\ReverseRouter;
use PSX\Http\Exception\FoundException;

/**
 * AuthorizeController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuthorizeController extends ControllerAbstract
{
    private const TEMPLATE_FILE = __DIR__ . '/../../resources/template/authorize.php';

    public function __construct(
        private readonly ReverseRouter $reverseRouter,
        private readonly View\App $appView,
        private readonly Service\User\Authorize $authorizeService,
        private readonly Service\User\Authenticator $authenticatorService,
        private readonly Service\System\FrameworkConfig $frameworkConfig,
    ) {
    }

    public function getPreFilter(): array
    {
        return [
            ...parent::getPreFilter(),
            Filter\Tenant::class,
            Filter\Firewall::class,
        ];
    }

    #[Get]
    #[Path('/authorization/authorize')]
    public function getAuthorize(
        #[Query('client_id')] ?string $clientId,
        #[Query('scope')] ?string $scope,
    ): Template {
        if (empty($clientId)) {
            return new Template(['error' => 'Provided no client id'], self::TEMPLATE_FILE, $this->reverseRouter, 400);
        }

        $data = [
            'app' => $this->appView->getEntityByAppKey($this->frameworkConfig->getTenantId(), $clientId, $scope),
        ];

        return new Template($data, self::TEMPLATE_FILE, $this->reverseRouter);
    }

    #[Post]
    #[Path('/authorization/authorize')]
    public function postAuthorize(
        #[Query('response_type')] ?string $responseType,
        #[Query('client_id')] ?string $clientId,
        #[Query('redirect_uri')] ?string $redirectUri,
        #[Query('scope')] ?string $scope,
        #[Query('state')] ?string $state,
        #[Body] Form $body,
    ): Template
    {
        $username = $body->get('username');
        $password = $body->get('password');
        $allow = $body->get('allow');

        if (empty($username) || empty($password)) {
            return new Template(['error' => 'Provided no username and password'], self::TEMPLATE_FILE, $this->reverseRouter, 400);
        }

        $userId = $this->authenticatorService->authenticate($username, $password);
        if (empty($userId)) {
            return new Template(['error' => 'Provided invalid credentials'], self::TEMPLATE_FILE, $this->reverseRouter, 400);
        }

        $app = $this->appView->getEntityByAppKey($this->frameworkConfig->getTenantId(), $clientId, $scope);

        $selectedScopes = [];
        $availableScopes = $app['scopes'] ?? [];
        foreach ($availableScopes as $scope) {
            if ($body->get('scope_' . $scope['id']) === 'on') {
                $selectedScopes[] = $scope['name'];
            }
        }

        if (empty($selectedScopes)) {
            return new Template(['error' => 'No scopes selected'], self::TEMPLATE_FILE, $this->reverseRouter, 400);
        }

        $request = new AuthorizeRequest();
        $request->setResponseType($responseType);
        $request->setClientId($clientId);
        $request->setRedirectUri($redirectUri);
        $request->setScope(implode(' ', $selectedScopes));
        $request->setState($state);
        $request->setAllow($allow === 'Allow');

        $response = $this->authorizeService->authorize($userId, $request);

        $redirectUri = $response->getRedirectUri();
        if (!empty($redirectUri)) {
            throw new FoundException($redirectUri);
        } else {
            $data = [
                'type' => $response->getType(),
                'code' => $response->getCode(),
                'error' => $response->getError(),
            ];

            return new Template($data, self::TEMPLATE_FILE, $this->reverseRouter);
        }
    }
}
