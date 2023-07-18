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

namespace Fusio\Impl\Provider\User;

use Fusio\Engine\User\UserDetails;

/**
 * OIDC
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class OIDC extends ProviderAbstract
{
    public function redirect(ConfigurationInterface $configuration): Url
    {
        $url = Url::parse($configuration->getAuthroizationUri());
        $url = $url->setParameters([
            'response_type' => 'code',
            'client_id' => $configuration->getClientId(),
            'state' => '',
            'redirect_uri' => '',
            'scope' => 'openid',
        ]);

        return $url;
    }

    public function requestUser(ConfigurationInterface $configuration, string $code, string $clientId, string $redirectUri): ?UserDetails
    {
        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $configuration->getClientSecret(),
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code'
        ];

        $idToken = $this->obtainIDToken($configuration->getTokenUri(), $params);
        if (empty($idToken)) {
            return null;
        }

        $data = JWT::decode($idToken);
        $claimMapping = $this->getClaimMapping($configuration);

        $id    = $data[self::CLAIM_ID] ?? null;
        $name  = $data[self::CLAIM_NAME] ?? null;
        $email = $data[self::CLAIM_EMAIL] ?? null;

        if (!empty($id) && !empty($name)) {
            return new UserDetails($id, $name, $email);
        } else {
            return null;
        }
    }

    protected function obtainIDToken(string $rawUrl, array $params, int $type = self::TYPE_POST): ?string
    {
        $data = $this->tokenRequest($rawUrl, $params, $type);
        return $this->parseIDToken($data);
    }

    private function parseIDToken(array $data): ?string
    {
        if (isset($data['id_token']) && is_string($data['id_token'])) {
            return $data['id_token'];
        } elseif (isset($data['error']) && is_string($data['error'])) {
            $error = Error::fromArray($data);
            throw new StatusCode\BadRequestException($error->getError() . ': ' . $error->getErrorDescription() . ' (' . $error->getErrorUri() . ')');
        } else {
            return null;
        }
    }
}
