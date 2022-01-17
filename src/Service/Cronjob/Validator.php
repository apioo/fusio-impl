<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Cronjob;

use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private const SPECIAL = [
        '@reboot',
        '@yearly',
        '@annually',
        '@monthly',
        '@weekly',
        '@daily',
        '@midnight',
        '@hourly',
    ];

    private const RULES = [
        [0, 59],
        [0, 23],
        [1, 31],
        [1, 12],
        [0, 7],
    ];

    private const CONVERT = [
        null,
        null,
        null,
        [1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'aug', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dec'],
        [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'],
    ];

    private const FIELDS = [
        'minute',
        'hour',
        'day',
        'month',
        'weekday',
    ];

    public static function assertCron(string $cron): void
    {
        if (empty($cron)) {
            throw new StatusCode\BadRequestException('Cron must not be empty');
        }

        if (in_array($cron, self::SPECIAL)) {
            return;
        }

        $cron = preg_replace('/\s+/', ' ', $cron);
        $parts = explode(' ', $cron);

        if (count($parts) != count(self::RULES)) {
            throw new StatusCode\BadRequestException('Cron must have exactly ' . count(self::RULES) . ' space separated fields');
        }

        foreach ($parts as $index => $part) {
            [$min, $max] = self::RULES[$index];

            try {
                self::validateField($part, $min, $max, self::CONVERT[$index]);
            } catch (\InvalidArgumentException $e) {
                throw new StatusCode\BadRequestException('Cron ' . self::FIELDS[$index] . ' ' . $e->getMessage());
            }
        }
    }

    private static function validateField($field, $min, $max, array $convert = null)
    {
        $parts = explode(',', $field);
        $entry = [];

        foreach ($parts as $part) {
            $matches = [];
            $part = trim($part);

            if ($part == '*') {
                $entry[] = '*';
                break;
            } elseif (preg_match('/^([0-9]+)$/', $part, $matches)) {
                $num = (int) $matches[1];

                self::validateRange($num, $min, $max);

                $entry[] = $num;
            } elseif (preg_match('/^([0-9]+)\-([0-9]+)$/', $part, $matches)){
                $from = (int) $matches[1];
                $to   = (int) $matches[2];

                self::validateRange($from, $min, $max);
                self::validateRange($to, $min, $max);

                $entry[] = $from . '-' . $to;
            } elseif (preg_match('/^(\*)\/([0-9]+)$/', $part, $matches)) {
                $step = (int) $matches[2];

                self::validateRange($step, $min, $max);

                $entry[] = '*/' . $step;
            } elseif (preg_match('/^([0-9]+)\-([0-9]+)\/([0-9]+)$/', $part, $matches)) {
                $from = (int) $matches[1];
                $to   = (int) $matches[2];
                $step = (int) $matches[3];

                self::validateRange($from, $min, $max);
                self::validateRange($to, $min, $max);
                self::validateRange($step, $min, $max);

                $entry[] = $from . '-' . $to . '/' . $step;
            } elseif ($convert !== null) {
                // convert names
                $index = array_search(strtolower($part), $convert);
                if ($index !== false) {
                    $entry[] = $index;
                }
            }
        }

        if (empty($entry)) {
            throw new \InvalidArgumentException('is not valid');
        }
    }

    private static function validateRange(int $value, int $min, int $max): void
    {
        if ($value < $min) {
            throw new \InvalidArgumentException('must be greater or equal ' . $min);
        } elseif ($value > $max) {
            throw new \InvalidArgumentException('must be lower or equal ' . $max);
        }
    }
}
