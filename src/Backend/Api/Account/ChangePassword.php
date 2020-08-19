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

namespace Fusio\Impl\Backend\Api\Account;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Backend\Model\Account_Credentials;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;

/**
 * ChangePassword
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ChangePassword extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $put = $builder->addMethod('PUT');
        $put->setSecurity(Authorization::BACKEND, ['backend.account']);
        $put->setRequest(Model\Account_Credentials::class);
        $put->addResponse(200, Model\Message::class);

        return $builder->getSpecification();
    }

    /**
     * @param Account_Credentials $record
     * @param HttpContextInterface $context
     * @return array|mixed
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->userService->changePassword(
            $record->getOldPassword(),
            $record->getNewPassword(),
            $record->getVerifyPassword(),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Password successful changed',
        );
    }
}
