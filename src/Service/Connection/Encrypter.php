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

namespace Fusio\Impl\Service\Connection;

use PSX\Json\Parser;
use PSX\OpenSsl\OpenSsl;

/**
 * Encrypter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        $iv   = random_bytes(openssl_cipher_iv_length($method));
        $data = Parser::encode($config);
        $data = OpenSsl::encrypt($data, $method, $secretKey, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv) . '.' . base64_encode($data);
    }

    public static function decrypt(mixed $data, string $secretKey)
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
