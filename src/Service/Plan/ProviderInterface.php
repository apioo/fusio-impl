<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Plan;

use Fusio\Impl\Service\Plan\Model\Product;
use Fusio\Impl\Service\Plan\Model\Transaction;

/**
 * ProviderInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
interface ProviderInterface
{
    /**
     * Creates a transaction and returns it with the fitting parameter which can
     * i.e. include an approval url to authorize this transaction
     * 
     * @param mixed $connection
     * @param \Fusio\Impl\Service\Plan\Model\Product $product
     * @return \Fusio\Impl\Service\Plan\Model\Transaction
     */
    public function prepare($connection, Product $product);

    /**
     * Is called after the user has approved the transaction. Checks whether the
     * transaction was successful and credits the points to the user
     * 
     * @param mixed $connection
     * @param \Fusio\Impl\Service\Plan\Model\Product $product
     * @param \Fusio\Impl\Service\Plan\Model\Transaction $transaction
     * @return \Fusio\Impl\Service\Plan\Model\Transaction
     */
    public function execute($connection, Product $product, Transaction $transaction);
}
