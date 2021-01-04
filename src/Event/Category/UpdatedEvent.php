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

namespace Fusio\Impl\Event\Category;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Category_Update;
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
     * @var Category_Update
     */
    private $category;

    /**
     * @var RecordInterface
     */
    private $existing;

    /**
     * @param Category_Update $category
     * @param RecordInterface $existing
     * @param UserContext $context
     */
    public function __construct(Category_Update $category, RecordInterface $existing, UserContext $context)
    {
        parent::__construct($context);

        $this->category = $category;
        $this->existing = $existing;
    }

    /**
     * @return Category_Update
     */
    public function getCategory(): Category_Update
    {
        return $this->category;
    }

    /**
     * @return RecordInterface
     */
    public function getExisting(): RecordInterface
    {
        return $this->existing;
    }
}
