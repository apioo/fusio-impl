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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use PSX\Framework\Environment\IPResolver;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Captcha
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Captcha
{
    public function __construct(
        private Service\Config $configService,
        private ClientInterface $httpClient,
        private IPResolver $ipResolver,
    ) {
    }

    public function assertCaptcha(?string $captcha): void
    {
        $secret = $this->configService->getValue('recaptcha_secret');
        if (!empty($secret)) {
            $this->verifyCaptcha($captcha, $secret);
        }
    }

    protected function verifyCaptcha(?string $captcha, string $secret): bool
    {
        if (empty($captcha)) {
            throw new StatusCode\BadRequestException('Invalid captcha');
        }

        $request = new PostRequest('https://www.google.com/recaptcha/api/siteverify', [], [
            'secret'   => $secret,
            'response' => $captcha,
            'remoteip' => $this->ipResolver->resolveByEnvironment(),
        ]);

        $response = $this->httpClient->request($request);

        if ($response->getStatusCode() == 200) {
            $data = Parser::decode((string) $response->getBody());
            if ($data->success === true) {
                return true;
            }
        }

        throw new StatusCode\BadRequestException('Invalid captcha');
    }
}
