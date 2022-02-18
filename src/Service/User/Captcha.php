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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Captcha
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Captcha
{
    private Service\Config $configService;
    private ClientInterface $httpClient;

    public function __construct(Service\Config $configService, ClientInterface $httpClient)
    {
        $this->configService = $configService;
        $this->httpClient = $httpClient;
    }

    public function assertCaptcha(?string $captcha): void
    {
        $secret = $this->configService->getValue('recaptcha_secret');
        if (!empty($secret)) {
            $this->verifyCaptcha($captcha, $secret);
        }
    }

    protected function verifyCaptcha(?string $captcha, string $secret)
    {
        if (empty($captcha)) {
            throw new StatusCode\BadRequestException('Invalid captcha');
        }

        $request = new PostRequest('https://www.google.com/recaptcha/api/siteverify', [], [
            'secret'   => $secret,
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
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
