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

        // @TODO for schema and action exports there can be a problem with the
        // order. In case a schema reference another schema and the depending
        // schema is listed afterwards the import will not work. The same
        // problem exists for actions. There must be a sorting to list entries
        // with dependencies at the bottom

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
    protected function exportType($type, $index, array &$result)
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

    /**
     * @param string $tableName
     * @param string $id
     * @param string $type
     * @return string
     */
    protected function getReference($tableName, $id, $type)
    {
        $name = $this->connection->fetchColumn('SELECT name FROM ' . $tableName . ' WHERE id = :id', ['id' => $id]);

        if (empty($name)) {
            $type = substr($tableName, 6);
            throw new \RuntimeException('Could not resolve ' . $type . ' ' . $id);
        }

        return $name;
    }
}
