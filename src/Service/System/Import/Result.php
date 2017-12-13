<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\System\Import;

/**
 * Result
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Result
{
    const ACTION_FAILED     = 'FAILED';
    const ACTION_UPDATED    = 'UPDATED';
    const ACTION_CREATED    = 'CREATED';
    const ACTION_REGISTERED = 'REGISTERED';
    const ACTION_EXECUTED   = 'EXECUTED';
    const ACTION_SKIPPED    = 'SKIPPED';

    /**
     * @var array
     */
    protected $result = [];

    /**
     * @param string $type
     * @param string $action
     * @param string $message
     */
    public function add($type, $action, $message)
    {
        if (!isset($this->result[$type])) {
            $this->result[$type] = [];
        }

        $this->result[$type][] = [$action, $message];
    }

    /**
     * @param \Fusio\Impl\Service\System\Import\Result $result
     */
    public function merge(Result $result)
    {
        $results = $result->getResults();
        foreach ($results as $type => $rows) {
            foreach ($rows as $row) {
                $this->add($type, $row[0], $row[1]);
            }
        }
    }

    /**
     * @return boolean
     */
    public function hasError()
    {
        foreach ($this->result as $type => $rows) {
            foreach ($rows as $row) {
                if ($row[0] === self::ACTION_FAILED) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @internal
     * @return array
     */
    public function getResults()
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->result as $type => $rows) {
            foreach ($rows as $row) {
                if ($row[0] === self::ACTION_FAILED) {
                    $errors[] = $row[1];
                }
            }
        }

        return $errors;
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        $logs = [];
        foreach ($this->result as $type => $rows) {
            foreach ($rows as $row) {
                $logs[] = '[' . $row[0] . '] ' . $type . ' ' . $row[1];
            }
        }

        return $logs;
    }
}
