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

use Fusio\Impl\Service\Captcha\Provider\FriendlyCaptcha;
use Fusio\Impl\Service\Captcha\Provider\FusioCaptcha;
use Fusio\Impl\Service\Captcha\Provider\HCaptcha;
use Fusio\Impl\Service\Captcha\Provider\ReCaptcha;

/**
 * CaptchaFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class CaptchaFactory
{
    public function __construct(
        private FriendlyCaptcha $friendlyCaptcha,
        private FusioCaptcha $fusioCaptcha,
        private HCaptcha $hCaptcha,
        private ReCaptcha $reCaptcha,
    ) {
    }

    public function factory(CaptchaType $type): CaptchaInterface
    {
        return match ($type) {
            CaptchaType::FRIENDLY => $this->friendlyCaptcha,
            CaptchaType::HCAPTCHA => $this->hCaptcha,
            CaptchaType::RECAPTCHA => $this->reCaptcha,
            CaptchaType::FUSIO => $this->fusioCaptcha,
        };
    }
}
