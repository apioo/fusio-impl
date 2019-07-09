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

namespace Fusio\Impl\Export\Schema\Rpc;

use PSX\Schema\Property;
use PSX\Schema\SchemaAbstract;

/**
 * Response
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Response extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('Export Rpc Response Return Success');
        $sb->string('jsonrpc');
        $sb->property('result')
            ->setTitle('Export Rpc Response Result')
            ->setDescription('Method result');
        $sb->integer('id');
        $rpcSuccess = $sb->getProperty();

        $error = $this->getSchemaBuilder('Export Rpc Response Error');
        $error->integer('code');
        $error->string('message');
        $error->property('data')
            ->setTitle('Export Rpc Response Error Data')
            ->setDescription('Error data');

        $sb = $this->getSchemaBuilder('Export Rpc Response Return Error');
        $sb->string('jsonrpc');
        $sb->objectType('error', $error->getProperty());
        $sb->integer('id');
        $rpcError = $sb->getProperty();

        $rpcReturn = Property::get()
            ->setTitle('Export Rpc Response Return')
            ->setOneOf([
                $rpcSuccess,
                $rpcError,
            ]);

        $batchCall = Property::getArray()
            ->setTitle('Export Rpc Response Batch')
            ->setItems($rpcReturn);

        return Property::get()
            ->setTitle('Export Rpc Response')
            ->setOneOf([
                $rpcReturn,
                $batchCall,
            ]);
    }
}
