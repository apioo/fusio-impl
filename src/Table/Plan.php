<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Table;

use Fusio\Engine\Model\Product;
use Fusio\Engine\Model\ProductInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\TableAbstract;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Plan extends Generated\PlanTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public const INTERVAL_NONE = 0;
    public const INTERVAL_1MONTH = 1;
    public const INTERVAL_3MONTH = 2;
    public const INTERVAL_6MONTH = 3;
    public const INTERVAL_12MONTH = 4;

    public function getProduct(int $planId): ProductInterface
    {
        $plan = $this->find($planId);

        if (empty($plan)) {
            throw new StatusCode\BadRequestException('Invalid plan id');
        }

        if ($plan['status'] != self::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid plan status');
        }

        return new Product(
            $plan['id'],
            $plan['name'],
            $plan['price'],
            $plan['points'],
            $plan['period_type']
        );
    }
}
