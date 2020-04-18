<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Consumer\Api\User;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Consumer\Schema;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Http\Environment\HttpContextInterface;

/**
 * PasswordReset
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PasswordReset extends SchemaApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\User\ResetPassword
     */
    protected $userResetPasswordService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::CONSUMER, ['consumer.user'])
            ->setRequest($this->schemaManager->getSchema(Schema\User\Email::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::CONSUMER, ['consumer.user'])
            ->setRequest($this->schemaManager->getSchema(Schema\User\PasswordReset::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $this->userResetPasswordService->resetPassword(
            $record->email,
            $record->captcha
        );

        return [
            'success' => true,
            'message' => 'Password reset email was send',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->userResetPasswordService->changePassword(
            $record->token,
            $record->newPassword
        );

        return [
            'success' => true,
            'message' => 'Password successful changed',
        ];
    }
}
