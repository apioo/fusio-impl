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

namespace Fusio\Impl\Service;

use Fusio\Engine\Model;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Rate_Allocation;
use Fusio\Model\Backend\Rate_Create;
use Fusio\Model\Backend\Rate_Update;
use Fusio\Impl\Event\Rate\CreatedEvent;
use Fusio\Impl\Event\Rate\DeletedEvent;
use Fusio\Impl\Event\Rate\UpdatedEvent;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Http\ResponseInterface;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Rate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Rate
{
    private Table\Rate $rateTable;
    private Table\Rate\Allocation $rateAllocationTable;
    private Table\Log $logTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Rate $rateTable, Table\Rate\Allocation $rateAllocationTable, Table\Log $logTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->rateTable           = $rateTable;
        $this->rateAllocationTable = $rateAllocationTable;
        $this->logTable            = $logTable;
        $this->eventDispatcher     = $eventDispatcher;
    }

    public function create(Rate_Create $rate, UserContext $context): int
    {
        // check whether rate exists
        if ($this->exists($rate->getName())) {
            throw new StatusCode\BadRequestException('Rate already exists');
        }

        // create rate
        try {
            $this->rateTable->beginTransaction();

            $record = new Table\Generated\RateRow([
                'status'     => Table\Rate::STATUS_ACTIVE,
                'priority'   => $rate->getPriority(),
                'name'       => $rate->getName(),
                'rate_limit' => $rate->getRateLimit(),
                'timespan'   => $rate->getTimespan(),
            ]);

            $this->rateTable->create($record);

            $rateId = $this->rateTable->getLastInsertId();
            $rate->setId($rateId);

            $this->handleAllocations($rateId, $rate->getAllocation());

            $this->rateTable->commit();
        } catch (\Throwable $e) {
            $this->rateTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($rate, $context));

        return $rateId;
    }

    public function update(int $rateId, Rate_Update $rate, UserContext $context): int
    {
        $existing = $this->rateTable->find($rateId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find rate');
        }

        if ($existing['status'] == Table\Rate::STATUS_DELETED) {
            throw new StatusCode\GoneException('Rate was deleted');
        }

        try {
            $this->rateTable->beginTransaction();

            // update rate
            $record = new Table\Generated\RateRow([
                'id'         => $existing['id'],
                'priority'   => $rate->getPriority(),
                'name'       => $rate->getName(),
                'rate_limit' => $rate->getRateLimit(),
                'timespan'   => $rate->getTimespan(),
            ]);

            $this->rateTable->update($record);

            $this->handleAllocations($existing['id'], $rate->getAllocation());

            $this->rateTable->commit();
        } catch (\Throwable $e) {
            $this->rateTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($rate, $existing, $context));

        return $rateId;
    }

    public function delete(int $rateId, UserContext $context): int
    {
        $existing = $this->rateTable->find($rateId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find rate');
        }

        $record = new Table\Generated\RateRow([
            'id'     => $existing['id'],
            'status' => Table\Rate::STATUS_DELETED,
        ]);

        $this->rateTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $rateId;
    }

    public function assertLimit(string $ip, int $routeId, Model\AppInterface $app, ?ResponseInterface $response = null): bool
    {
        $rate = $this->rateAllocationTable->getRateForRequest($routeId, $app);
        if (empty($rate)) {
            return false;
        }

        $count     = $this->getRequestCount($ip, $rate['timespan'], $app);
        $rateLimit = (int) $rate['rate_limit'];

        if ($response !== null) {
            $response->setHeader('X-RateLimit-Limit', $rateLimit);
            $response->setHeader('X-RateLimit-Remaining', $rateLimit - $count);
        }

        if ($count >= $rateLimit) {
            throw new StatusCode\ClientErrorException('Rate limit exceeded', 429);
        }

        return true;
    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->notEquals('status', Table\Rate::STATUS_DELETED);
        $condition->equals('name', $name);

        $app = $this->rateTable->findOneBy($condition);

        if (!empty($app)) {
            return $app['id'];
        } else {
            return false;
        }
    }

    protected function getRequestCount(string $ip, string $timespan, Model\AppInterface $app): int
    {
        if (empty($timespan)) {
            return 0;
        }

        $now  = new \DateTime();
        $past = new \DateTime();
        $past->sub(new \DateInterval($timespan));

        $condition = new Condition();
        // we count only requests to the default category
        $condition->equals('category_id', 1);

        if ($app->isAnonymous()) {
            $condition->equals('ip', $ip);
        } else {
            $condition->equals('app_id', $app->getId());
        }

        $condition->between('date', $past->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));

        return $this->logTable->getCount($condition);
    }

    /**
     * @param Rate_Allocation[] $allocations
     */
    protected function handleAllocations(int $rateId, ?array $allocations = null): void
    {
        $this->rateAllocationTable->deleteAllFromRate($rateId);

        if (!empty($allocations)) {
            foreach ($allocations as $allocation) {
                $this->rateAllocationTable->create(new Table\Generated\RateAllocationRow([
                    'rate_id'       => $rateId,
                    'route_id'      => $allocation->getRouteId(),
                    'app_id'        => $allocation->getAppId(),
                    'authenticated' => $allocation->getAuthenticated(),
                    'parameters'    => $allocation->getParameters(),
                ]));
            }
        }
    }
}
