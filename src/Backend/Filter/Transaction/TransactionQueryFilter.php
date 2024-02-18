<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Backend\Filter\Transaction;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use PSX\Sql\Condition;

/**
 * TransactionQueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TransactionQueryFilter extends DateQueryFilter
{
    private ?int $invoiceId = null;
    private ?int $status = null;
    private ?string $provider = null;

    public function __construct(?int $invoiceId, ?int $status, ?string $provider, \DateTimeImmutable $from, \DateTimeImmutable $to, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($from, $to, $startIndex, $count, $search, $sortBy, $sortOrder);

        $this->invoiceId = $invoiceId;
        $this->status = $status;
        $this->provider = $provider;
    }

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

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = parent::getCondition($columnMapping, $alias);
        $alias = $this->getAlias($alias);

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

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $invoiceId = self::toInt($request->get('invoiceId'));
        $status = self::toInt($request->get('status'));
        $provider = $request->get('provider');

        $search = $arguments['search'] ?? null;
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

            $arguments['search'] = null;
        }

        $arguments['invoiceId'] = $invoiceId;
        $arguments['status'] = $status;
        $arguments['provider'] = $provider;

        return $arguments;
    }
}
