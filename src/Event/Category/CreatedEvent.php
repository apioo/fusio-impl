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
use Fusio\Model\Backend\Category_Create;
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
     * @var Category_Create
     */
    private $category;

    /**
     * @param Category_Create $category
     * @param \Fusio\Impl\Authorization\UserContext $context
     */
    public function __construct(Category_Create $category, UserContext $context)
    {
        parent::__construct($context);

        $this->category = $category;
    }

    /**
     * @return Category_Create
     */
    public function getCategory(): Category_Create
    {
        return $this->category;
    }
}
