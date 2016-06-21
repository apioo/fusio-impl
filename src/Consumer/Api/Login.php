<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Consumer\Api;

use DateTime;
use Fusio\Impl\Authorization\ProtectionTrait;
use Fusio\Impl\Table\App;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Record\RecordInterface;
use PSX\Validate\Filter as PSXFilter;
use PSX\Http\Exception as StatusCode;
use PSX\Framework\Loader\Context;
use PSX\Sql\Condition;
use PSX\Uri\Uri;
use PSX\Uri\Url;
use PSX\Validate\Validate;

/**
 * Login
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Login extends SchemaApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\Consumer
     */
    protected $consumer;

    /**
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setRequest($this->schemaManager->getSchema('Fusio\Impl\Consumer\Schema\Login'))
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Consumer\Schema\JWT'))
        );

        return $resource;
    }

    /**
     * Returns the POST response
     *
     * @param \PSX\Record\RecordInterface $record
     * @return array|\PSX\Record\RecordInterface
     */
    protected function doPost($record)
    {
        $token = $this->consumer->login($record->name, $record->password);

        if (!empty($token)) {
            return [
                'token' => $token,
            ];
        } else {
            throw new StatusCode\UnauthorizedException('Invalid name or password', 'Basic');
        }
    }
}
