<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Service;

use DateTime;
use Doctrine\DBAL\Connection;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Base;
use InvalidArgumentException;
use PSX\Dispatch;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\Stream\TempStream;
use PSX\Json;

/**
 * System
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class System
{
    protected $dispatch;
    protected $connection;

    protected $types = ['connection', 'schema', 'action', 'routes'];
    protected $accessToken;

    public function __construct(Dispatch $dispatch, Connection $connection)
    {
        $this->dispatch   = $dispatch;
        $this->connection = $connection;
    }

    public function export()
    {
        $data = new stdClass();

        $classes = $this->getClasses('fusio_action_class');
        if (!empty($classes)) {
            $data->actionClass = $classes;
        }

        $classes = $this->getClasses('fusio_connection_class');
        if (!empty($classes)) {
            $data->connectionClass = $classes;
        }

        foreach ($this->types as $type) {
            $data->$type = $this->exportType($type);
        }

        return Json::encode($data);
    }

    public function import(stdClass $data)
    {
        $data = Json::decode($data, false);
        foreach ($this->types as $type) {
            if (isset($data->$type)) {
                $this->import($type, $data->$type);
            }
        }
    }

    protected function exportType($type)
    {
        $index  = 0;
        $result = [];

        $collection = $this->doRequest('GET', $type . '?startIndex=' . $index);

        $count      = isset($collection->totalResults) ? $collection->totalResults : 0;

        if (isset($collection->entry) && is_array($collection->entry)) {
            foreach ($collection->entry as $entry) {
                $entity = $this->doRequest('GET', $type. '/' . $entry->id);

                // remove id
                unset($entity->id);

                // @TODO replace id with name references

                $result[] = $entity;
            }
        }

        return $result;
    }

    protected function importType($type, $data)
    {
        return $this->doRequest('POST', $type, $data);
    }

    protected function assertType($type)
    {
        if (!in_array($type, $this->types)) {
            throw new InvalidArgumentException('Invalid type');
        }
    }

    protected function getClasses($table)
    {
        $classes = [];
        $result  = $this->connection->fetchAll('SELECT class FROM ' . $table);

        foreach ($result as $row) {
            $classes[] = $row['class'];
        }

        return $classes;
    }

    protected function doRequest($method, $endpoint, $body = null)
    {
        $header   = ['User-Agent' => 'Fusio-System v' . Base::getVersion(), 'Authorization' => 'Bearer ' . $this->getAccessToken()];
        $body     = $body !== null ? Json::encode($body) : null;
        $request  = new Request(new Url('http://127.0.0.1/backend/' . $endpoint), $method, $header, $body);
        $response = new Response();
        $response->setBody(new TempStream(fopen('php://memory', 'r+')));

        $this->dispatch->route($request, $response);

        $body = (string) $response->getBody();
        $data = Json::decode($body, false);

        return $data;
    }

    protected function getAccessToken()
    {
        if (empty($this->accessToken)) {
            // insert access token
            $token  = TokenGenerator::generateToken();
            $expire = new DateTime('+30 minute');
            $now    = new DateTime();

            $this->connection->insert('fusio_app_token', [
                'appId'  => 1,
                'userId' => 1,
                'status' => 1,
                'token'  => $token,
                'scope'  => 'backend',
                'ip'     => '127.0.0.1',
                'expire' => $expire->format('Y-m-d H:i:s'),
                'date'   => $now->format('Y-m-d H:i:s'),
            ]);

            return $this->accessToken = $token;
        } else {
            return $this->accessToken;
        }
    }
}
