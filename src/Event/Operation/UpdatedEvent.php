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

namespace Fusio\Impl\Event\Operation;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;
use Fusio\Impl\Table\Generated\OperationRow;
use Fusio\Model\Backend\OperationUpdate;

/**
 * UpdatedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UpdatedEvent extends EventAbstract
{
    private OperationUpdate $operation;
    private OperationRow $existing;

    public function __construct(OperationUpdate $operation, OperationRow $existing, UserContext $context)
    {
        parent::__construct($context);

        $this->operation = $operation;
        $this->existing  = $existing;
    }

    public function getOperation(): OperationUpdate
    {
        return $this->operation;
    }

    public function getExisting(): OperationRow
    {
        return $this->existing;
    }
}
