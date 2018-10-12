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

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\TransactionInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Payment\PrepareContext;
use Fusio\Engine\Payment\ProviderInterface;

/**
 * Paypal
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Paypal implements ProviderInterface
{
    public function prepare($connection, ProductInterface $product, TransactionInterface $transaction, PrepareContext $context)
    {
        // here the payment provider needs to create the transaction using the
        // remote connection and return an approval url

        $approvalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-60385559L1062554J';

        return $approvalUrl;
    }

    public function execute($connection, ProductInterface $product, TransactionInterface $transaction, ParametersInterface $parameters)
    {
        // here the payment provider needs to execute the transaction and set
        // the transaction to approved

        $transaction->setStatus(TransactionInterface::STATUS_APPROVED);
    }
}
