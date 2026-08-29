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

namespace Fusio\Impl\Service\Captcha;

use Fusio\Impl\Service;
use PSX\Framework\Environment\IPResolver;
use PSX\Http\Exception as StatusCode;

/**
 * Verifier
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Verifier
{
    public function __construct(
        private Service\Config $configService,
        private CaptchaFactory $factory,
        private IPResolver $ipResolver,
    ) {
    }

    public function assertCaptcha(?string $captcha): void
    {
        $captchaType = $this->configService->getString('captcha_type');
        $captchaSecret = $this->configService->getString('captcha_secret');
        $recaptchaSecret = $this->configService->getString('recaptcha_secret');

        $provider = null;
        $secret = null;
        if (!empty($recaptchaSecret)) {
            // legacy recaptcha fallback the new captcha system is only active if the old recaptcha secret is empty
            $provider = $this->factory->factory(CaptchaType::RECAPTCHA);
            $secret = $recaptchaSecret;
        } elseif (!empty($captchaType)) {
            $type = CaptchaType::tryFrom($captchaType) ?? throw new StatusCode\InternalServerErrorException('Wrong captcha type was configured');
            $provider = $this->factory->factory($type);
            $secret = $captchaSecret;
        }

        if ($provider instanceof CaptchaInterface) {
            if (empty($captcha)) {
                throw new StatusCode\BadRequestException('Invalid captcha');
            }

            $result = $provider->verify(
                $captcha,
                (string) $secret,
                $this->ipResolver->resolveByEnvironment()
            );

            if (!$result) {
                throw new StatusCode\BadRequestException('Invalid captcha');
            }
        }
    }
}
