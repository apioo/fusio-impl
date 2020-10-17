<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Rpc\Middleware;

use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Schema\Loader;
use PSX\Http\Exception as StatusCode;
use PSX\Record\RecordInterface;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\Visitor\TypeVisitor;

/**
 * ValidateSchema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ValidateSchema
{
    /**
     * @var Loader
     */
    private $schemaLoader;

    /**
     * @var SchemaTraverser
     */
    private $schemaTraverser;

    public function __construct(Loader $schemaLoader)
    {
        $this->schemaLoader    = $schemaLoader;
        $this->schemaTraverser = new SchemaTraverser();
    }

    public function __invoke(RecordInterface $arguments, array $method, Context $context)
    {
        if (!empty($method['request'])) {
            if (!$arguments->hasProperty('payload')) {
                throw new StatusCode\BadRequestException('No payload argument provided');
            }

            $schema  = $this->schemaLoader->getSchema($method['request']);
            $payload = $this->schemaTraverser->traverse($arguments->getProperty('payload'), $schema, new TypeVisitor());

            $arguments->setProperty('payload', $payload);
        } else {
            $arguments->setProperty('payload', null);
        }
    }
}
