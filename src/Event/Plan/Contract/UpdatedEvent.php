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

namespace Fusio\Impl\Event\Plan\Contract;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;
use Fusio\Model\Backend\Plan_Contract_Update;
use PSX\Record\RecordInterface;

/**
 * UpdatedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class UpdatedEvent extends EventAbstract
{
    /**
     * @var Plan_Contract_Update
     */
    private $contract;

    /**
     * @var RecordInterface
     */
    private $existing;

    /**
     * @param Plan_Contract_Update $contract
     * @param RecordInterface $existing
     * @param UserContext $context
     */
    public function __construct(Plan_Contract_Update $contract, RecordInterface $existing, UserContext $context)
    {
        parent::__construct($context);

        $this->contract = $contract;
        $this->existing = $existing;
    }

    /**
     * @return Plan_Contract_Update
     */
    public function getContract(): Plan_Contract_Update
    {
        return $this->contract;
    }

    /**
     * @return RecordInterface
     */
    public function getExisting(): RecordInterface
    {
        return $this->existing;
    }
}
