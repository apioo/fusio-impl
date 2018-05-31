<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Api\Action;

use Fusio\Adapter\Php\Action\PhpSandbox;
use Fusio\Impl\Backend\Filter\PrimaryKey;
use Fusio\Impl\Table;
use PSX\Api\Resource\MethodAbstract;
use PSX\Schema\Validation\Field;
use PSX\Schema\Validation\Validator;
use PSX\Http\Exception as StatusCode;

/**
 * ValidatorTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait ValidatorTrait
{
    protected function getValidator(MethodAbstract $method)
    {
        return new Validator(array(
            new Field('/id', [new PrimaryKey($this->tableManager->getTable(Table\Action::class))]),
        ));
    }

    protected function assertSandboxAccess($record)
    {
        $class = ltrim($record->class, '\\');

        if (!$this->config->get('fusio_php_sandbox') && strcasecmp($class, PhpSandbox::class) == 0) {
            throw new StatusCode\BadRequestException('Usage of the PHP sandbox feature is disabled. To activate it set the key "fusio_php_sandbox" in the configuration.php file to "true"');
        }
    }
}
