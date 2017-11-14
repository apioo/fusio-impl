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

namespace Fusio\Impl\Loader;

use Doctrine\Common\Annotations\Reader;
use Fusio\Impl\Table;
use PSX\Api\Generator;
use PSX\Api\GeneratorInterface;

/**
 * GeneratorFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GeneratorFactory extends \PSX\Api\GeneratorFactory
{
    /**
     * @var \Fusio\Impl\Table\Scope
     */
    protected $scopeTable;

    public function __construct(Table\Scope $scopeTable, Reader $reader, $namespace, $url, $dispatch)
    {
        parent::__construct($reader, $namespace, $url, $dispatch);

        $this->scopeTable = $scopeTable;
    }

    protected function configure(GeneratorInterface $generator)
    {
        if ($generator instanceof Generator\OpenAPI) {
            $authUrl  = $this->url . '/developer/auth';
            $tokenUrl = $this->url . '/' . $this->dispatch . 'authorization/token';
            $scopes   = $this->getScopes();

            $generator->setTitle('Fusio');
            $generator->setAuthorizationFlow('OAuth2', Generator\OpenAPI::FLOW_AUTHORIZATION_CODE, $authUrl, $tokenUrl, null, $scopes);
        } elseif ($generator instanceof Generator\Raml) {
            $generator->setTitle('Fusio');
        } elseif ($generator instanceof Generator\Swagger) {
            $generator->setTitle('Fusio');
        }
    }

    private function getScopes()
    {
        $result = [];
        $scopes = $this->scopeTable->getAll(0, 1024);
        foreach ($scopes as $scope) {
            $result[$scope['name']] = $scope['description'];
        }

        return $result;
    }
}
