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

use Doctrine\DBAL\Connection;
use Fusio\Engine\Form;
use Fusio\Engine\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;

/**
 * SystemAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class SystemAbstract
{
    const COLLECTION_SIZE = 16;

    const TYPE_SCOPE = 'scope';
    const TYPE_USER = 'user';
    const TYPE_APP = 'app';
    const TYPE_CONFIG = 'config';
    const TYPE_CONNECTION = 'connection';
    const TYPE_SCHEMA = 'schema';
    const TYPE_ACTION = 'action';
    const TYPE_ROUTE = 'routes';
    const TYPE_CRONJOB = 'cronjob';
    const TYPE_RATE = 'rate';
    const TYPE_EVENT = 'event';

    /**
     * @var \Fusio\Impl\Service\System\ApiExecutor
     */
    protected $apiExecutor;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Engine\Parser\ParserInterface
     */
    protected $actionParser;

    /**
     * @var \Fusio\Engine\Parser\ParserInterface
     */
    protected $connectionParser;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $types = [
        self::TYPE_SCOPE,
        self::TYPE_CONNECTION,
        self::TYPE_SCHEMA,
        self::TYPE_ACTION,
        self::TYPE_ROUTE,
        self::TYPE_CRONJOB,
        self::TYPE_RATE,
        self::TYPE_USER,
        self::TYPE_APP,
        self::TYPE_EVENT,
    ];

    /**
     * @param \Fusio\Impl\Service\System\ApiExecutor $apiExecutor
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Fusio\Engine\Parser\ParserInterface $actionParser
     * @param \Fusio\Engine\Parser\ParserInterface $connectionParser
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(ApiExecutor $apiExecutor, Connection $connection, ParserInterface $actionParser, ParserInterface $connectionParser, LoggerInterface $logger)
    {
        $this->apiExecutor      = $apiExecutor;
        $this->connection       = $connection;
        $this->actionParser     = $actionParser;
        $this->connectionParser = $connectionParser;
        $this->logger           = $logger;
    }

    protected function doRequest($method, $endpoint, $body = null)
    {
        return $this->apiExecutor->request($method, $endpoint, $body);
    }
}
