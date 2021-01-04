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

namespace Fusio\Impl\Table;

use Fusio\Engine\Model\Product;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\TableAbstract;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Plan extends TableAbstract
{
    const STATUS_ACTIVE  = 1;
    const STATUS_DELETED = 0;

    const INTERVAL_NONE = 0;
    const INTERVAL_1MONTH = 1;
    const INTERVAL_3MONTH = 2;
    const INTERVAL_6MONTH = 3;
    const INTERVAL_12MONTH = 4;

    public function getName()
    {
        return 'fusio_plan';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'status' => self::TYPE_INT,
            'name' => self::TYPE_VARCHAR,
            'description' => self::TYPE_VARCHAR,
            'price' => self::TYPE_FLOAT,
            'points' => self::TYPE_INT,
            'period_type' => self::TYPE_INT,
        );
    }

    /**
     * @param integer $planId
     * @return \Fusio\Engine\Model\ProductInterface
     */
    public function getProduct($planId)
    {
        $plan = $this->get($planId);

        if (empty($plan)) {
            throw new StatusCode\BadRequestException('Invalid plan id');
        }

        if ($plan['status'] != self::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid plan status');
        }

        $product = new Product();
        $product->setId($plan['id']);
        $product->setName($plan['name']);
        $product->setPrice($plan['price']);
        $product->setPoints($plan['points']);
        $product->setInterval($plan['period_type']);

        return $product;
    }
}
