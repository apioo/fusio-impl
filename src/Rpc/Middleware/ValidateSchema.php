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

namespace Fusio\Impl\Rpc\Middleware;

use Fusio\Engine\Record\PassthruRecord;
use Fusio\Engine\Request\RpcRequest;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Schema\Loader;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\Visitor\TypeVisitor;

/**
 * ValidateSchema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ValidateSchema
{
    private const SCHEMA_PASSTHRU = 'Passthru';

    private Loader $schemaLoader;
    private SchemaTraverser $schemaTraverser;

    public function __construct(Loader $schemaLoader)
    {
        $this->schemaLoader    = $schemaLoader;
        $this->schemaTraverser = new SchemaTraverser();
    }

    public function __invoke(RpcRequest $request, Context $context)
    {
        $method    = $context->getMethod();
        $arguments = $request->getArguments();

        if (!empty($method['request'])) {
            if (!$arguments->hasProperty('payload')) {
                throw new StatusCode\BadRequestException('No payload argument provided');
            }

            if ($method['request'] == self::SCHEMA_PASSTHRU) {
                $payload = new PassthruRecord($arguments->getProperty('payload'));
            } else {
                $schema  = $this->schemaLoader->getSchema($method['request']);
                $payload = $this->schemaTraverser->traverse($arguments->getProperty('payload'), $schema, new TypeVisitor());
            }

            $arguments->setProperty('payload', $payload);
        } else {
            $arguments->setProperty('payload', null);
        }
    }
}
