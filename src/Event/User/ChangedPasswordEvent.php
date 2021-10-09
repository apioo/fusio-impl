<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Event\User;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;
use Fusio\Model\Backend\Account_ChangePassword;

/**
 * ChangedPasswordEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ChangedPasswordEvent extends EventAbstract
{
    /**
     * @var Account_ChangePassword
     */
    protected $changePassword;

    /**
     * @param Account_ChangePassword $changePassword
     * @param \Fusio\Impl\Authorization\UserContext $context
     */
    public function __construct(Account_ChangePassword $changePassword, UserContext $context)
    {
        parent::__construct($context);

        $this->changePassword = $changePassword;
    }

    /**
     * @return Account_ChangePassword
     */
    public function getChangePassword(): Account_ChangePassword
    {
        return $this->changePassword;
    }
}
