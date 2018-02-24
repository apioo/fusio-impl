<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Consumer\Api\App\Meta;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\Schema;
use Fusio\Impl\Consumer\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Loader\Context;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Entity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends ConsumerApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::CONSUMER, ['consumer'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\App\Meta::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $app = $this->tableManager->getTable(View\App::class)->getEntityByAppKey(
            $context->getParameter('client_id'),
            $context->getParameter('scope')
        );

        if (!empty($app)) {
            if ($app['status'] == Table\App::STATUS_DELETED) {
                throw new StatusCode\GoneException('App was deleted');
            }

            return $app;
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }
}
