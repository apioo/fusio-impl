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

namespace Fusio\Impl\Backend\Action\Connection;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Provider\ConnectionProviderParser;
use Fusio\Impl\Service\Connection\Token;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\TableManagerInterface;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Get extends ActionAbstract
{
    /**
     * @var View\Connection
     */
    private $table;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConnectionProviderParser
     */
    private $connectionParser;

    /**
     * @var Token
     */
    private $tokenService;

    public function __construct(TableManagerInterface $tableManager, Config $config, ConnectionProviderParser $connectionParser, Token $tokenService)
    {
        $this->table = $tableManager->getTable(View\Connection::class);
        $this->config = $config;
        $this->connectionParser = $connectionParser;
        $this->tokenService = $tokenService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $connection = $this->table->getEntityWithConfig(
            $request->get('connection_id'),
            $this->config->get('fusio_project_key'),
            $this->connectionParser
        );

        if (empty($connection)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($connection['status'] == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        if ($this->tokenService->isValid($request->get('connection_id'))) {
            // in case the connection supports the oauth2 flow we add a button to start the authorization code flow
            $connection['oauth2'] = true;
        }

        return $connection;
    }
}
