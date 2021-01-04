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

namespace Fusio\Impl\Event\Rate;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Rate_Update;
use Fusio\Impl\Event\EventAbstract;
use PSX\Record\RecordInterface;

/**
 * UpdatedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UpdatedEvent extends EventAbstract
{
    /**
     * @var Rate_Update
     */
    private $rate;

    /**
     * @var RecordInterface
     */
    private $existing;

    /**
     * @param Rate_Update $rate
     * @param RecordInterface $existing
     * @param UserContext $context
     */
    public function __construct(Rate_Update $rate, RecordInterface $existing, UserContext $context)
    {
        parent::__construct($context);

        $this->rate     = $rate;
        $this->existing = $existing;
    }

    /**
     * @return Rate_Update
     */
    public function getRate(): Rate_Update
    {
        return $this->rate;
    }

    /**
     * @return RecordInterface
     */
    public function getExisting(): RecordInterface
    {
        return $this->existing;
    }
}
