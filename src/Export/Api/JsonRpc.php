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

namespace Fusio\Impl\Export\Api;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Export;
use Fusio\Impl\Export\Schema;
use Fusio\Impl\Rpc\Invoker;
use Fusio\Impl\Table;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Json\Rpc\Server;

/**
 * JsonRpc
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class JsonRpc extends SchemaApiAbstract implements DocumentedInterface
{
    /**
     * @Inject
     * @var \PSX\Sql\TableManager
     */
    protected $tableManager;

    /**
     * @var \Fusio\Impl\Framework\Loader\Context
     */
    protected $context;

    /**
     * @Inject
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @Inject
     * @var \Fusio\Engine\Processor
     */
    protected $processor;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Routes\Method
     */
    protected $routesMethodService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Rate
     */
    protected $rateService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Security\TokenValidator
     */
    protected $securityTokenValidator;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Plan\Payer
     */
    protected $planPayerService;

    /**
     * @return array
     */
    public function getPreFilter()
    {
        $filter = [];

        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        return $filter;
    }

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->setRequest($this->schemaManager->getSchema(Schema\Rpc\Request::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Rpc\Response::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $server = new Server(new Invoker(
            $this->processor,
            $this->securityTokenValidator,
            $this->rateService,
            $this->planPayerService,
            $this->tableManager->getTable(Table\Routes\Method::class),
            $this->tableManager->getTable(Table\Schema::class),
            $this->config,
            $this->request
        ));

        return $server->invoke($record);
    }
}
