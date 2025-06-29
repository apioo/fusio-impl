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

namespace Fusio\Impl\Service\User;

use PSX\Http\Client\ClientInterface;
use PSX\Http\RequestInterface;
use PSX\Json\Parser;

/**
 * ReCaptcha
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly abstract class CaptchaAbstract implements CaptchaInterface
{
    public function __construct(private ClientInterface $httpClient)
    {
    }

    protected function request(RequestInterface $request, string $successProperty = 'success'): bool
    {
        $response = $this->httpClient->request($request);
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        try {
            $data = Parser::decode((string) $response->getBody());
        } catch (\JsonException) {
            return false;
        }

        if (!$data instanceof \stdClass) {
            return false;
        }

        $success = $data->{$successProperty} ?? null;
        if ($success !== true) {
            return false;
        }

        return true;
    }
}
