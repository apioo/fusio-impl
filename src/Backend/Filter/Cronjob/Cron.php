<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Filter\Cronjob;

use PSX\Validate\FilterAbstract;

/**
 * Cron
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Cron extends FilterAbstract
{
    /**
     * @var array
     */
    protected $special = [
        '@reboot',
        '@yearly',
        '@annually',
        '@monthly',
        '@weekly',
        '@daily',
        '@midnight',
        '@hourly',
    ];

    /**
     * @var array
     */
    protected $rules = [
        [0, 59],
        [0, 23],
        [1, 31],
        [1, 12],
        [0, 7],
    ];

    /**
     * @var array
     */
    protected $convert = [
        null,
        null,
        null,
        [1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'aug', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dec'],
        [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'],
    ];

    /**
     * @var array
     */
    protected $fields = [
        'minute',
        'hour',
        'day',
        'month',
        'weekday',
    ];

    /**
     * @var string
     */
    protected $errorMessage = '%s is not a valid cron expression';

    /**
     * @param mixed $value
     * @return mixed
     */
    public function apply($value)
    {
        if (!empty($value)) {
            if (in_array($value, $this->special)) {
                return $value;
            }

            $value = preg_replace('/\s+/', ' ', $value);
            $parts = explode(' ', $value);

            if (count($parts) != count($this->rules)) {
                $this->errorMessage = '%s must have exactly ' . count($this->rules) . ' space separated fields';
                return false;
            }

            $result = [];
            foreach ($parts as $index => $part) {
                list($min, $max) = $this->rules[$index];

                try {
                    $result[] = $this->validateField($part, $min, $max, $this->convert[$index]);
                } catch (\InvalidArgumentException $e) {
                    $this->errorMessage = '%s ' . $this->fields[$index] . ' ' . $e->getMessage();
                    return false;
                }
            }

            return implode(' ', $result);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    private function validateField($field, $min, $max, array $convert = null)
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

                $this->validateRange($num, $min, $max);

                $entry[] = $num;
            } elseif (preg_match('/^([0-9]+)\-([0-9]+)$/', $part, $matches)){
                $from = (int) $matches[1];
                $to   = (int) $matches[2];

                $this->validateRange($from, $min, $max);
                $this->validateRange($to, $min, $max);

                $entry[] = $from . '-' . $to;
            } elseif (preg_match('/^(\*)\/([0-9]+)$/', $part, $matches)) {
                $step = (int) $matches[2];

                $this->validateRange($step, $min, $max);

                $entry[] = '*/' . $step;
            } elseif (preg_match('/^([0-9]+)\-([0-9]+)\/([0-9]+)$/', $part, $matches)) {
                $from = (int) $matches[1];
                $to   = (int) $matches[2];
                $step = (int) $matches[3];

                $this->validateRange($from, $min, $max);
                $this->validateRange($to, $min, $max);
                $this->validateRange($step, $min, $max);

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

        return implode(',', $entry);
    }

    private function validateRange($value, $min, $max)
    {
        if ($value < $min) {
            throw new \InvalidArgumentException('must be greater or equal ' . $min);
        } elseif ($value > $max) {
            throw new \InvalidArgumentException('must be lower or equal ' . $max);
        }
    }
}
