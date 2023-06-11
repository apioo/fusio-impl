<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Scope;

use Fusio\Impl\Table;
use Fusio\Model\Backend\Scope;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Scope $scopeTable;

    public function __construct(Table\Scope $scopeTable)
    {
        $this->scopeTable = $scopeTable;
    }

    public function assert(Scope $scope, ?Table\Generated\ScopeRow $existing = null): void
    {
        $name = $scope->getName();
        if ($name !== null) {
            $this->assertName($name, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Scope name must not be empty');
        }

        if ($existing !== null) {
            // check whether this is a system scope
            if (in_array($existing->getId(), [1, 2, 3])) {
                throw new StatusCode\BadRequestException('It is not possible to change this scope');
            }
        }
    }

    private function assertName(string $name, ?Table\Generated\ScopeRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid scope name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->scopeTable->findOneByName($name)) {
            throw new StatusCode\BadRequestException('Scope already exists');
        }
    }
}
