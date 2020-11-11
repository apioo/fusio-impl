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

namespace Fusio\Impl\Service\System;

use PSX\Json\Parser;
use stdClass;

/**
 * Export
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Export extends SystemAbstract
{
    public function export()
    {
        $data = new stdClass();

        foreach ($this->types as $type) {
            $result = array();

            $this->exportType($type, 0, $result);

            if (count($result) > 0) {
                $data->$type = $result;
            }
        }

        return Parser::encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @param string $type
     * @param integer $index
     * @param array $result
     */
    private function exportType(string $type, int $index, array &$result)
    {
        $collection = $this->doRequest('GET', $type . '?startIndex=' . $index);
        $count      = isset($collection->totalResults) ? $collection->totalResults : 0;
        $startIndex = isset($collection->startIndex)   ? $collection->startIndex   : 0;

        if (isset($collection->entry) && is_array($collection->entry)) {
            foreach ($collection->entry as $entry) {
                $entity = $this->doRequest('GET', $type. '/' . $entry->id);

                // check whether the API returned an error
                if (isset($entity->success) && $entity->success === false && isset($entity->message)) {
                    throw new \RuntimeException('Exporting ' . $type . ' failed, the API responded with: ' . $entity->message);
                }

                $result[] = $this->transform($type, $entity);
            }
        }

        if ($count > count($result)) {
            $this->exportType($type, $index + self::COLLECTION_SIZE, $result);
        }
    }

    private function transform(string $type, stdClass $entity): stdClass
    {
        if ($type === self::TYPE_SCOPE) {
            return $this->transformScope($entity);
        } elseif ($type === self::TYPE_USER) {
            return $this->transformUser($entity);
        } elseif ($type === self::TYPE_APP) {
            return $this->transformApp($entity);
        } elseif ($type === self::TYPE_CONNECTION) {
            return $this->transformConnection($entity);
        } elseif ($type === self::TYPE_SCHEMA) {
            return $this->transformSchema($entity);
        } elseif ($type === self::TYPE_ACTION) {
            return $this->transformAction($entity);
        } elseif ($type === self::TYPE_ROUTE) {
            return $this->transformRoute($entity);
        } elseif ($type === self::TYPE_CRONJOB) {
            return $this->transformCronjob($entity);
        } elseif ($type === self::TYPE_RATE) {
            return $this->transformRate($entity);
        } elseif ($type === self::TYPE_EVENT) {
            return $this->transformEvent($entity);
        } else {
            return $entity;
        }
    }

    private function transformConnection(stdClass $entity)
    {
        unset($entity->id);

        return $entity;
    }

    private function transformSchema(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    private function transformAction(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    private function transformRoute(stdClass $entity)
    {
        unset($entity->id);

        return $entity;
    }

    private function transformCronjob(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);
        unset($entity->executeDate);
        unset($entity->exitCode);
        unset($entity->errors);

        return $entity;
    }

    private function transformRate(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    private function transformApp(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->appKey);
        unset($entity->appSecret);
        unset($entity->tokens);

        return $entity;
    }

    private function transformUser(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->apps);

        return $entity;
    }

    private function transformScope(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->routes);

        return $entity;
    }

    private function transformEvent(stdClass $entity)
    {
        unset($entity->id);

        return $entity;
    }
}
