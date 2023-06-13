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

namespace Fusio\Impl\Service\Connection;

use PSX\Json\Parser;
use PSX\OpenSsl\OpenSsl;

/**
 * Encrypter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Encrypter
{
    public static function encrypt(mixed $config, string $secretKey)
    {
        if (empty($config)) {
            return null;
        }

        $method = self::getMethodForKey($secretKey);
        $length = openssl_cipher_iv_length($method);

        if ($length <= 0) {
            throw new \RuntimeException('Could not get cipher length');
        }

        $iv   = random_bytes($length);
        $data = Parser::encode($config);
        $data = OpenSsl::encrypt($data, $method, $secretKey, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv) . '.' . base64_encode($data);
    }

    public static function decrypt(mixed $data, string $secretKey): array
    {
        if (empty($data)) {
            return [];
        }

        if (is_resource($data)) {
            $data = stream_get_contents($data, -1, 0);
        }

        $parts = explode('.', $data, 2);
        if (count($parts) !== 2) {
            return [];
        }

        [$iv, $data] = $parts;

        $method = self::getMethodForKey($secretKey);
        $config = OpenSsl::decrypt(base64_decode($data), $method, $secretKey, OPENSSL_RAW_DATA, base64_decode($iv));
        $config = Parser::decode($config, true);

        return $config;
    }

    private static function getMethodForKey(string $secretKey): string
    {
        $len = strlen($secretKey);
        if ($len >= 16) {
            return 'AES-128-CBC';
        } else {
            throw new \RuntimeException('Length of provided secret key is too short must be at least 16 bytes');
        }
    }
}
