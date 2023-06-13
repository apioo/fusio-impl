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

namespace Fusio\Impl\Table;

use Fusio\Impl\Table\Generated\CategoryRow;
use Fusio\Impl\Table\Generated\ConfigRow;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Config extends Generated\ConfigTable
{
    public const FORM_STRING   = 1;
    public const FORM_BOOLEAN  = 2;
    public const FORM_NUMBER   = 3;
    public const FORM_DATETIME = 4;
    public const FORM_EMAIL    = 5;
    public const FORM_TEXT     = 6;
    public const FORM_SECRET   = 7;

    public function findOneByIdentifier(string $id): ?ConfigRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByName(urldecode(substr($id, 1)));
        } else {
            return $this->find((int) $id);
        }
    }

    public function getValue($name)
    {
        return $this->connection->fetchAssociative('SELECT id, value, type FROM fusio_config WHERE name = :name', [
            'name' => $name
        ]);
    }
}
