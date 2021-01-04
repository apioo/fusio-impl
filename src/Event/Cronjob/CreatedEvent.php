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

namespace Fusio\Impl\Event\Cronjob;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Cronjob_Create;
use Fusio\Impl\Event\EventAbstract;

/**
 * CreatedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CreatedEvent extends EventAbstract
{
    /**
     * @var Cronjob_Create
     */
    private $cronjob;

    /**
     * @param Cronjob_Create $cronjob
     * @param UserContext $context
     */
    public function __construct(Cronjob_Create $cronjob, UserContext $context)
    {
        parent::__construct($context);

        $this->cronjob = $cronjob;
    }

    /**
     * @return Cronjob_Create
     */
    public function getCronjob(): Cronjob_Create
    {
        return $this->cronjob;
    }
}
