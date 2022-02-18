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

namespace Fusio\Impl\Event\Plan\Invoice;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;
use Fusio\Impl\Table\Generated\PlanInvoiceRow;
use Fusio\Model\Backend\Plan_Invoice_Update;
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
    private Plan_Invoice_Update $invoice;
    private PlanInvoiceRow $existing;

    public function __construct(Plan_Invoice_Update $invoice, PlanInvoiceRow $existing, UserContext $context)
    {
        parent::__construct($context);

        $this->invoice  = $invoice;
        $this->existing = $existing;
    }

    public function getInvoice(): Plan_Invoice_Update
    {
        return $this->invoice;
    }

    public function getExisting(): PlanInvoiceRow
    {
        return $this->existing;
    }
}
