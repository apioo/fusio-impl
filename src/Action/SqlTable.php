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

namespace Fusio\Impl\Action;

use Doctrine\DBAL\Connection;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface as ResponseFactoryInterface;
use Fusio\Impl\ConfigurationException;
use PSX\Http\Exception as StatusCode;

/**
 * SqlTable
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlTable implements ActionInterface
{
    /**
     * @Inject
     * @var \Fusio\Engine\ConnectorInterface
     */
    protected $connector;

    /**
     * @Inject
     * @var \Fusio\Engine\Response\FactoryInterface
     */
    protected $response;
    
    public function getName()
    {
        return 'SQL-Table';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $connection = $this->connector->getConnection($configuration->get('connection'));

        if ($connection instanceof Connection) {
            $table      = $configuration->get('table');
            $columns    = explode(',', $configuration->get('columns'));
            $primaryKey = $configuration->get('primaryKey');

            if (empty($table)) {
                throw new ConfigurationException('No table name provided');
            }

            if (empty($columns)) {
                throw new ConfigurationException('No columns provided');
            }

            if (empty($primaryKey)) {
                throw new ConfigurationException('No primary key provided');
            }

            switch ($request->getMethod()) {
                case 'GET':
                    return $this->doGet($request, $connection, $table, $columns, $primaryKey);
                    break;

                case 'POST':
                    return $this->doPost($request, $connection, $table, $columns, $primaryKey);
                    break;

                case 'PUT':
                    return $this->doPut($request, $connection, $table, $columns, $primaryKey);
                    break;

                case 'DELETE':
                    return $this->doDelete($request, $connection, $table, $columns, $primaryKey);
                    break;
            }

            $id = $request->getUriFragment('id');
            if (empty($id)) {
                throw new StatusCode\MethodNotAllowedException('Method not allowed', ['GET', 'POST']);
            } else {
                throw new StatusCode\MethodNotAllowedException('Method not allowed', ['GET', 'PUT', 'DELETE']);
            }
        } else {
            throw new ConfigurationException('Given connection must be a DBAL connection');
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newConnection('connection', 'Connection', 'The SQL connection which should be used'));
        $builder->add($elementFactory->newInput('table', 'Table', 'text', 'Name of the database table'));
        $builder->add($elementFactory->newInput('columns', 'Columns', 'text', 'Comma seperated list of columns which you want to expose i.e. <code>id,title,date</code>'));
        $builder->add($elementFactory->newInput('primaryKey', 'Primary Key', 'text', 'Name of the primary key column'));
    }

    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function setResponse(ResponseFactoryInterface $response)
    {
        $this->response = $response;
    }

    protected function doGet(RequestInterface $request, Connection $connection, $table, array $columns, $primaryKey)
    {
        $id = $request->getUriFragment('id');
        if (empty($id)) {
            return $this->doGetCollection(
                $request,
                $connection,
                $table,
                $columns,
                $primaryKey
            );
        } else {
            return $this->doGetEntity(
                $id,
                $connection,
                $table,
                $columns,
                $primaryKey
            );
        }
    }

    protected function doGetCollection(RequestInterface $request, Connection $connection, $table, array $columns, $primaryKey)
    {
        $startIndex  = (int) $request->getParameter('startIndex');
        $count       = (int) $request->getParameter('count');
        $sortBy      = $request->getParameter('sortBy');
        $sortOrder   = $request->getParameter('sortOrder');
        $filterBy    = $request->getParameter('filterBy');
        $filterOp    = $request->getParameter('filterOp');
        $filterValue = $request->getParameter('filterValue');

        $qb = $connection->createQueryBuilder();
        $qb->select($columns);
        $qb->from($table);

        if (!empty($sortBy) && !empty($sortOrder) && in_array($sortBy, $columns)) {
            $sortOrder = strtoupper($sortOrder);
            $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC';

            $qb->orderBy($sortBy, $sortOrder);
        } else {
            $qb->orderBy($primaryKey, 'DESC');
        }

        if (!empty($filterBy) && !empty($filterOp) && !empty($filterValue) && in_array($filterBy, $columns)) {
            switch ($filterOp) {
                case 'contains':
                    $qb->where($filterBy . ' LIKE :filter');
                    $qb->setParameter('filter', '%' . $filterValue . '%');
                    break;

                case 'equals':
                    $qb->where($filterBy . ' = :filter');
                    $qb->setParameter('filter', $filterValue);
                    break;

                case 'startsWith':
                    $qb->where($filterBy . ' LIKE :filter');
                    $qb->setParameter('filter', $filterValue . '%');
                    break;

                case 'present':
                    $qb->where($filterBy . ' IS NOT NULL');
                    break;
            }
        }

        $startIndex = $startIndex < 0 ? 0 : $startIndex;
        $count      = $count >= 1 && $count <= 32 ? $count : 16;

        $qb->setFirstResult($startIndex);
        $qb->setMaxResults($count);

        $totalCount = (int) $connection->fetchColumn('SELECT COUNT(' . $primaryKey . ') FROM ' . $table);
        $result     = $connection->fetchAll($qb->getSQL(), $qb->getParameters());

        return $this->response->build(200, [], [
            'totalResults' => $totalCount,
            'itemsPerPage' => $count,
            'startIndex'   => $startIndex,
            'entry'        => $result,
        ]);
    }

    protected function doGetEntity($id, Connection $connection, $table, array $columns, $primaryKey)
    {
        $qb = $connection->createQueryBuilder();
        $qb->select($columns);
        $qb->from($table);
        $qb->where($primaryKey . ' = :id');
        $qb->setParameter('id', $id);

        $row = $connection->fetchAssoc($qb->getSQL(), $qb->getParameters());

        if (!empty($row)) {
            return $this->response->build(200, [], $row);
        } else {
            throw new StatusCode\NotFoundException('Entry not available');
        }
    }

    protected function doPost(RequestInterface $request, Connection $connection, $table, array $columns, $primaryKey)
    {
        $id = $request->getUriFragment('id');
        if (empty($id)) {
            $body = $request->getBody();
            $data = [];
            foreach ($body as $key => $value) {
                if (in_array($key, $columns)) {
                    $data[$key] = $value;
                }
            }

            if (empty($data)) {
                throw new StatusCode\BadRequestException('No valid data provided');
            }

            $connection->insert($table, $data);

            return $this->response->build(201, [], [
                'success' => true,
                'message' => 'Entry successful created'
            ]);
        } else {
            throw new StatusCode\MethodNotAllowedException('Method not allowed', ['GET', 'PUT', 'DELETE']);
        }
    }

    protected function doPut(RequestInterface $request, Connection $connection, $table, array $columns, $primaryKey)
    {
        $id = $request->getUriFragment('id');
        if (!empty($id)) {
            $body = $request->getBody();
            $data = [];
            foreach ($body as $key => $value) {
                if (in_array($key, $columns)) {
                    $data[$key] = $value;
                }
            }

            if (empty($data)) {
                throw new StatusCode\BadRequestException('No valid data provided');
            }

            $connection->update($table, $data, [$primaryKey => $id]);

            return $this->response->build(200, [], [
                'success' => true,
                'message' => 'Entry successful updated'
            ]);
        } else {
            throw new StatusCode\MethodNotAllowedException('Method not allowed', ['GET', 'PUT', 'DELETE']);
        }
    }

    protected function doDelete(RequestInterface $request, Connection $connection, $table, array $columns, $primaryKey)
    {
        $id = $request->getUriFragment('id');
        if (!empty($id)) {
            $connection->delete($table, [$primaryKey => $id]);

            return $this->response->build(200, [], [
                'success' => true,
                'message' => 'Entry successful deleted'
            ]);
        } else {
            throw new StatusCode\MethodNotAllowedException('Method not allowed', ['GET', 'PUT', 'DELETE']);
        }
    }
}
