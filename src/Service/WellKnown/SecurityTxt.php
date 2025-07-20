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

namespace Fusio\Impl\Service\WellKnown;

use Fusio\Impl\Service;

/**
 * SecurityTxt
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class SecurityTxt
{
    public function __construct(private Service\Config $configService)
    {
    }

    public function build(): string
    {
        $contactUrl = $this->configService->getValue('info_contact_url') ?: null;
        $contactEmail = $this->configService->getValue('info_contact_email') ?: null;

        $expires = (new \DateTime())->add(new \DateInterval('P1M'))->format('Y-m-d\T00:00:00.000\Z');

        $lines = [];
        if (!empty($contactEmail)) {
            $lines[] = 'Contact: mailto:' . $contactEmail;
        }

        if (!empty($contactUrl)) {
            $lines[] = 'Contact: ' . $contactUrl;
        }

        $lines[] = 'Contact: mailto:security@fusio-project.org';
        $lines[] = 'Contact: https://github.com/apioo/fusio';
        $lines[] = 'Contact: https://chrisk.app/';
        $lines[] = 'Expires: ' . $expires;
        $lines[] = 'Encryption: https://chrisk.app/pub.key';
        $lines[] = 'Preferred-Languages: en';

        return implode("\n", $lines);
    }
}
