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

namespace Fusio\Impl\Backend\View\Transaction;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View\QueryFilterAbstract;
use PSX\Sql\Condition;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class QueryFilter extends QueryFilterAbstract
{
    protected ?int $invoiceId = null;
    protected ?int $status = null;
    protected ?string $provider = null;

    public function getInvoiceId(): ?int
    {
        return $this->invoiceId;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getCondition(?string $alias = null): Condition
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

        if (!empty($this->invoiceId)) {
            $condition->equals($alias . 'invoice_id', $this->invoiceId);
        }

        if (!empty($this->status)) {
            $condition->equals($alias . 'status', $this->status);
        }

        if (!empty($this->provider)) {
            $condition->like($alias . 'provider', $this->provider);
        }

        return $condition;
    }

    protected function getDateColumn(): string
    {
        return 'insert_date';
    }

    public static function create(RequestInterface $request): static
    {
        $filter    = parent::create($request);
        $invoiceId = $request->get('invoiceId');
        $status    = $request->get('status');
        $provider  = $request->get('provider');
        $search    = $request->get('search');

        // parse search if available
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
                if (is_numeric($part)) {
                    $status = intval($part);
                } else {
                    $provider = $part;
                }
            }
        }

        $filter->invoiceId = $invoiceId;
        $filter->status    = $status;
        $filter->provider  = $provider;

        return $filter;
    }
}
