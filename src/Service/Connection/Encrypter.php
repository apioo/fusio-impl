<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use PSX\Json\Parser;

/**
 * Encrypter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Encrypter
{
    public static function encrypt($config, string $secretKey)
    {
        if (empty($config)) {
            return null;
        }

        return Crypto::encrypt(
            Parser::encode($config),
            Key::loadFromAsciiSafeString($secretKey)
        );
    }

    public static function decrypt($data, string $secretKey)
    {
        if (empty($data)) {
            return [];
        }

        if (is_resource($data)) {
            $data = stream_get_contents($data, -1, 0);
        }

        $config = Crypto::decrypt(
            $data,
            Key::loadFromAsciiSafeString($secretKey)
        );

        return Parser::decode($config, true);
    }
}