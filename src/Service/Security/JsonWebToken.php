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

namespace Fusio\Impl\Service\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PSX\Framework\Config\ConfigInterface;

/**
 * JsonWebToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class JsonWebToken
{
    private const ALGO = 'HS256';

    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->config->get('fusio_project_key'), self::ALGO);
    }

    public function decode(string $jwt): \stdClass
    {
        return JWT::decode($jwt, new Key($this->config->get('fusio_project_key'), self::ALGO));
    }
}
