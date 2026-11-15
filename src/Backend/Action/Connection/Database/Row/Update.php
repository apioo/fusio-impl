<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Backend\Action\Connection\Database\Row;

use Fusio\Adapter\Sql\Action\SqlUpdate;
use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ExcludedInterface;
use Fusio\Engine\Parameters;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Update
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Update extends SqlUpdate implements ExcludedInterface
{
    public function __construct(RuntimeInterface $runtime, private FrameworkConfig $frameworkConfig)
    {
        parent::__construct($runtime);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        if (!$this->frameworkConfig->isConnectionEnabled()) {
            throw new StatusCode\ServiceUnavailableException('Database is not enabled, please change the setting "fusio_connection" at the configuration.php to "true" in order to activate the database');
        }

        $configuration = new Parameters([
            'connection' => $request->get('connection_id'),
            'table' => $request->get('table_name'),
        ]);

        return parent::handle($request, $configuration, $context);
    }
}
