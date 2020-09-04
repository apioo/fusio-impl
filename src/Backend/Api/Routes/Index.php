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

namespace Fusio\Impl\Backend\Api\Routes;

use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Provider\ProviderConfig;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;

/**
 * Index
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Index extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Provider\ProviderLoader
     */
    protected $providerLoader;

    /**
     * @Inject
     * @var \PSX\Dependency\AutowireResolverInterface
     */
    protected $containerAutowireResolver;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.routes']);
        $get->addResponse(200, Model\Route_Index::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    public function doGet(HttpContextInterface $context)
    {
        $classes = $this->providerLoader->getConfig()->getClasses(ProviderConfig::TYPE_ROUTES);
        $result  = [];

        foreach ($classes as $name => $class) {
            $provider = $this->containerAutowireResolver->getObject($class);
            if ($provider instanceof ProviderInterface) {
                $result[] = [
                    'name'  => $provider->getName(),
                    'class' => $name,
                ];
            }
        }

        return [
            'providers' => $result
        ];
    }
}
