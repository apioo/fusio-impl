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

namespace Fusio\Impl\Tests;

/**
 * Normalizer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Normalizer
{
    public static function normalize(string $data): string
    {
        $data = self::normalizeUuid($data);
        $data = self::normalizeDateTime($data);
        $data = self::normalizeHttpDateTime($data);
        return $data;
    }

    public static function normalizeUuid(string $data): string
    {
        return preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/m', '[uuid]', $data);
    }

    public static function normalizeDateTime(string $data): string
    {
        return preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $data);
    }

    public static function normalizeHttpDateTime(string $data): string
    {
        return preg_replace('/\w{3}, \d{2} \w{3} \d{4} \d{2}:\d{2}:\d{2} GMT/m', '[datetime]', $data);
    }
}
