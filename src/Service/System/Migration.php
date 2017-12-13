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

namespace Fusio\Impl\Service\System;

use Fusio\Engine\Connector;
use Fusio\Impl\Service\System\Import\Result;
use Fusio\Impl\Table;
use Psr\Log\LoggerInterface;
use PSX\Sql\Condition;

/**
 * Migration
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Migration
{
    /**
     * @var \Fusio\Engine\Connector
     */
    protected $connector;

    /**
     * @var \Fusio\Impl\Table\Deploy\Migration
     */
    protected $deployTable;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Fusio\Engine\Connector $connector
     * @param \Fusio\Impl\Table\Deploy\Migration $deployTable
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Connector $connector, Table\Deploy\Migration $deployTable, LoggerInterface $logger)
    {
        $this->connector   = $connector;
        $this->deployTable = $deployTable;
        $this->logger      = $logger;
    }

    /**
     * @param array $migration
     * @param null $basePath
     * @return \Fusio\Impl\Service\System\Import\Result
     */
    public function execute(array $migration, $basePath = null)
    {
        $result = new Result();

        foreach ($migration as $connectionId => $definitionFiles) {
            if (is_array($definitionFiles)) {
                foreach ($definitionFiles as $definitionFile) {
                    $path = $basePath . '/' . $definitionFile;
                    if (is_file($path)) {
                        try {
                            $this->executeDefinition($connectionId, $path, $definitionFile, $result);
                        } catch (\Throwable $e) {
                            $this->logger->error($e->getMessage());

                            $result->add(Deploy::TYPE_MIGRATION, Result::ACTION_FAILED, $connectionId . ' ' . $definitionFile . ': ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function executeDefinition($connectionId, $path, $definitionFile, Result $result)
    {
        $connection = $this->connector->getConnection($connectionId);
        $hash       = sha1_file($path);

        // check whether the script was already executed
        $condition = new Condition();
        $condition->equals('connection', $connectionId);
        $condition->equals('file', $definitionFile);

        $deploy = $this->deployTable->getOneBy($condition);

        if (empty($deploy)) {
            // execute if the file was not already executed
            $strategy = Migration\Factory::getStrategy($connection);
            $strategy->execute($connection, $path);

            // insert migration
            $this->deployTable->create([
                'connection' => $connectionId,
                'file' => $definitionFile,
                'fileHash' => $hash,
                'executeDate' => new \DateTime(),
            ]);

            $result->add(Deploy::TYPE_MIGRATION, Result::ACTION_EXECUTED, $connectionId . ' ' . $definitionFile);
        } else {
            if ($deploy['fileHash'] != $hash) {
                $result->add(Deploy::TYPE_MIGRATION, Result::ACTION_SKIPPED, $connectionId . ' ' . $definitionFile . ' (The file was already executed, but the content has changed)');
            }
        }
    }
}
