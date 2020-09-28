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

namespace Fusio\Impl\Consumer\Api\User;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\Model;
use Fusio\Impl\Consumer\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Authorize
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Authorize extends ConsumerApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\User\Authorize
     */
    protected $userAuthorizeService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::CONSUMER, ['consumer.user']);
        $query = $get->setQueryParameters('Consumer_User_Authorize_Query');
        $query->addString('client_id');
        $query->addString('scope');
        $get->addResponse(200, Model\Authorize_Meta::class);

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::CONSUMER, ['consumer.user']);
        $post->setRequest(Model\Authorize_Request::class);
        $post->addResponse(200, Model\Authorize_Response::class);

        return $builder->getSpecification();
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

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        return $this->userAuthorizeService->authorize(
            $this->context->getUserId(),
            $record
        );
    }
}
