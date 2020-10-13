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

namespace Fusio\Impl\Backend\Action\Import;

use Doctrine\DBAL\Connection;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Model\Adapter;
use Fusio\Impl\Service\System\Import;
use PSX\Json\Parser;

/**
 * Process
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Process extends ActionAbstract
{
    /**
     * @var Import
     */
    private $importService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Import $importService, Connection $connection)
    {
        $this->importService = $importService;
        $this->connection = $connection;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $body = $request->getPayload();

        assert($body instanceof Adapter);

        try {
            $this->connection->beginTransaction();

            $data   = Parser::encode($body);
            $result = $this->importService->import($data);

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Import successful',
                'result'  => $result->getLogs(),
            ];
        } catch (\Throwable $e) {
            $this->connection->rollback();

            throw $e;
        }
    }
}
