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

namespace Fusio\Impl\Service;

use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Model\System\CaptchaChallenge;

/**
 * Captcha
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Captcha
{
    private const string ALGORITHM = 'SHA-256';
    private const int DEFAULT_EXPIRE_SECONDS = 300;

    public function __construct(private FrameworkConfig $config)
    {
    }

    public function challenge(int $expireSeconds = self::DEFAULT_EXPIRE_SECONDS): CaptchaChallenge
    {
        $salt = bin2hex(random_bytes(16));
        $maxNumber = 100_000;
        $secretNumber = random_int(1000, $maxNumber);

        $challenge = hash('sha256', $salt . $secretNumber);
        $expires = time() + $expireSeconds;

        $dataToSign = implode("\n", [
            self::ALGORITHM,
            $challenge,
            (string) $maxNumber,
            $salt,
            (string) $expires
        ]);

        $signature = hash_hmac('sha256', $dataToSign, $this->config->getProjectKey());

        $result = new CaptchaChallenge();
        $result->setAlgorithm(self::ALGORITHM);
        $result->setChallenge($challenge);
        $result->setMaxnumber($maxNumber);
        $result->setSalt($salt);
        $result->setSignature($signature);
        $result->setExpires($expires);
        return $result;
    }

    public function verify(string $base64Payload): bool
    {
        $json = base64_decode($base64Payload, true);
        if (!$json) {
            return false;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['challenge']) || !isset($data['number'])) {
            return false;
        }

        $algorithm  = (string) ($data['algorithm'] ?? '');
        $challenge  = (string) ($data['challenge'] ?? '');
        $maxNumber  = (int) ($data['maxnumber'] ?? 0);
        $salt       = (string) ($data['salt'] ?? '');
        $signature  = (string) ($data['signature'] ?? '');
        $userNumber = (int) $data['number'];
        $expires    = isset($data['expires']) ? (int) $data['expires'] : null;

        if ($algorithm !== self::ALGORITHM) {
            return false;
        }

        if ($expires !== null && time() > $expires) {
            return false;
        }

        if ($userNumber < 0 || ($maxNumber > 0 && $userNumber > $maxNumber)) {
            return false;
        }

        $paramsToSign = [
            self::ALGORITHM,
            $challenge,
            (string) $maxNumber,
            $salt
        ];

        if ($expires !== null) {
            $paramsToSign[] = (string) $expires;
        }

        $expectedSignature = hash_hmac('sha256', implode("\n", $paramsToSign), $this->config->getProjectKey());
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        $computedChallenge = hash('sha256', $salt . $userNumber);

        return hash_equals($computedChallenge, $challenge);
    }

    public function solve(CaptchaChallenge $challenge): string
    {
        $solutionNumber = null;
        $salt = (string) $challenge->getSalt();
        $targetChallenge = (string) $challenge->getChallenge();
        $maxNumber = (int) $challenge->getMaxnumber();

        for ($i = 0; $i <= $maxNumber; $i++) {
            if (hash('sha256', $salt . $i) === $targetChallenge) {
                $solutionNumber = $i;
                break;
            }
        }

        return base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => $solutionNumber,
        ]));
    }
}
