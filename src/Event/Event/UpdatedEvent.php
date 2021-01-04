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

namespace Fusio\Impl\Event\Event;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Event_Update;
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
     * @var Event_Update
     */
    private $event;

    /**
     * @var RecordInterface
     */
    private $existing;

    /**
     * @param Event_Update $event
     * @param RecordInterface $existing
     * @param UserContext $context
     */
    public function __construct(Event_Update $event, RecordInterface $existing, UserContext $context)
    {
        parent::__construct($context);

        $this->event    = $event;
        $this->existing = $existing;
    }

    /**
     * @return Event_Update
     */
    public function getEvent(): Event_Update
    {
        return $this->event;
    }

    /**
     * @return RecordInterface
     */
    public function getExisting(): RecordInterface
    {
        return $this->existing;
    }
}
