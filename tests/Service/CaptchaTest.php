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

namespace Fusio\Impl\Tests\Service;

use Fusio\Impl\Service\Captcha;
use Fusio\Model\System\CaptchaChallenge;
use PHPUnit\Framework\TestCase;
use PSX\Framework\Test\Environment;

/**
 * CaptchaTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CaptchaTest extends TestCase
{
    public function testChallenge(): void
    {
        $captchaService = Environment::getService(Captcha::class);
        $challenge = $captchaService->challenge();

        $this::assertInstanceOf(CaptchaChallenge::class, $challenge);
        $this::assertSame('SHA-256', $challenge->getAlgorithm());
        $this::assertSame(64, strlen((string) $challenge->getChallenge()));
        $this::assertSame(32, strlen((string) $challenge->getSalt()));
        $this::assertSame(64, strlen((string) $challenge->getSignature()));
        $this::assertSame(100_000, $challenge->getMaxnumber());
        $this::assertGreaterThan(time(), $challenge->getExpires());
    }

    public function testVerifyValidPayload(): void
    {
        $captchaService = Environment::getService(Captcha::class);
        $challenge = $captchaService->challenge();

        $solutionNumber = $this->solveChallenge(
            (string) $challenge->getSalt(),
            (string) $challenge->getChallenge(),
            (int) $challenge->getMaxnumber()
        );
        $this::assertNotNull($solutionNumber, 'Failed to solve challenge for testing');

        $payload = base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => $solutionNumber,
        ]));

        $this::assertTrue($captchaService->verify($payload));
    }

    public function testVerifyInvalidNumber(): void
    {
        $captchaService = Environment::getService(Captcha::class);
        $challenge = $captchaService->challenge();

        $payload = base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => 999_999, // Incorrect number
        ]));

        $this::assertFalse($captchaService->verify($payload));
    }

    public function testVerifyTamperedSignature(): void
    {
        $captchaService = Environment::getService(Captcha::class);
        $challenge = $captchaService->challenge();

        $solutionNumber = $this->solveChallenge(
            (string) $challenge->getSalt(),
            (string) $challenge->getChallenge(),
            (int) $challenge->getMaxnumber()
        );

        $payload = base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => 'invalid_tampered_signature_string_0000000000000000000000000000000',
            'expires'   => $challenge->getExpires(),
            'number'    => $solutionNumber,
        ]));

        $this::assertFalse($captchaService->verify($payload));
    }

    public function testVerifyExpiredChallenge(): void
    {
        $captchaService = Environment::getService(Captcha::class);

        // Request a challenge expired 10 seconds ago
        $challenge = $captchaService->challenge(-10);

        $solutionNumber = $this->solveChallenge(
            (string) $challenge->getSalt(),
            (string) $challenge->getChallenge(),
            (int) $challenge->getMaxnumber()
        );

        $payload = base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => $solutionNumber,
        ]));

        $this::assertFalse($captchaService->verify($payload));
    }

    public function testVerifyOutOfBoundsNumber(): void
    {
        $captchaService = Environment::getService(Captcha::class);
        $challenge = $captchaService->challenge();

        // Testing negative solution number
        $payloadNegative = base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => -5,
        ]));

        $this::assertFalse($captchaService->verify($payloadNegative));

        // Testing number exceeding maxnumber
        $payloadExceeds = base64_encode((string) json_encode([
            'algorithm' => $challenge->getAlgorithm(),
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => ((int) $challenge->getMaxnumber()) + 1,
        ]));

        $this::assertFalse($captchaService->verify($payloadExceeds));
    }

    public function testVerifyInvalidAlgorithm(): void
    {
        $captchaService = Environment::getService(Captcha::class);
        $challenge = $captchaService->challenge();

        $payload = base64_encode((string) json_encode([
            'algorithm' => 'SHA-512', // Unsupported algorithm
            'challenge' => $challenge->getChallenge(),
            'maxnumber' => $challenge->getMaxnumber(),
            'salt'      => $challenge->getSalt(),
            'signature' => $challenge->getSignature(),
            'expires'   => $challenge->getExpires(),
            'number'    => 1000,
        ]));

        $this::assertFalse($captchaService->verify($payload));
    }

    public function testVerifyMalformedPayload(): void
    {
        $captchaService = Environment::getService(Captcha::class);

        $this::assertFalse($captchaService->verify('not_base64_json!@#$'));
        $this::assertFalse($captchaService->verify(base64_encode('invalid_json_string')));
        $this::assertFalse($captchaService->verify(base64_encode((string) json_encode(['foo' => 'bar']))));
    }

    /**
     * Solves the proof-of-work puzzle synchronously for testing purposes.
     */
    private function solveChallenge(string $salt, string $targetChallenge, int $maxNumber): ?int
    {
        for ($i = 0; $i <= $maxNumber; $i++) {
            if (hash('sha256', $salt . $i) === $targetChallenge) {
                return $i;
            }
        }

        return null;
    }
}