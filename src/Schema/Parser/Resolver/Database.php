<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Schema\Parser\Resolver;

use Doctrine\DBAL\Connection;
use PSX\Json\Parser;
use PSX\Schema\Parser\TypeSchema\ResolverInterface;
use PSX\Uri\Uri;

/**
 * Database
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Database implements ResolverInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Uri $uri, ?string $basePath = null): \stdClass
    {
        $result = $this->connection->fetchAll('SELECT source FROM fusio_schema WHERE name LIKE :name', ['name' => ltrim($uri->getPath(), '/')]);

        $definitions = [];
        foreach ($result as $row) {
            $data = Parser::decode($row['source']);
            if (isset($data->definitions)) {
                $definitions = array_merge($definitions, (array) $data->definitions);
            }
        }

        return (object) [
            'definitions' => (object) $definitions,
        ];
    }
}
