<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Tenant;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception\BadRequestException;

/**
 * Remove
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Remove implements ActionInterface
{
    private Service\Tenant $tenantService;

    public function __construct(Service\Tenant $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $tenantId = $request->get('tenant_id');
        if (empty($tenantId)) {
            throw new BadRequestException('Missing tenant id');
        }

        $this->tenantService->remove($tenantId, $context);

        return new HttpResponse(200, [], [
            'success' => true,
            'message' => 'Tenant successfully deleted',
        ]);
    }
}
