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

namespace Fusio\Impl\Backend\Api\Action;

use Fusio\Engine\Form\Container;
use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Schema\Property;

/**
 * Form
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Form extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Engine\Parser\ParserInterface
     */
    protected $actionParser;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.action']);
        $query = $get->setQueryParameters('Action_Form_Query');
        $query->addString('class');
        $get->addResponse(200, $this->schemaManager->getSchema(Schema\Form\Container::class));

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    public function doGet(HttpContextInterface $context)
    {
        $className = $context->getParameter('class');
        $form      = $this->actionParser->getForm($className);

        if ($form instanceof Container) {
            return $form;
        } else {
            return new Container();
        }
    }
}
