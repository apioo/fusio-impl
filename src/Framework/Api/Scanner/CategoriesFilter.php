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

namespace Fusio\Impl\Framework\Api\Scanner;

use PSX\Api\OperationInterface;
use PSX\Api\Scanner\FilterInterface;

/**
 * GroupFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CategoriesFilter implements FilterInterface
{
    private array $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function match(OperationInterface $operation): bool
    {
        // we dont need to filter any values since we already filter at the query
        return true;
    }

    public function getId(): string
    {
        return implode(',', $this->ids);
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
