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

namespace Fusio\Impl\Service\Plan;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;

/**
 * Payer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Payer
{
    private Table\User $userTable;
    private Table\Plan\Usage $usageTable;
    private Service\Config $configService;
    private Service\User\Mailer $mailer;

    public function __construct(Table\User $userTable, Table\Plan\Usage $usageTable, Service\Config $configService, Service\User\Mailer $mailer)
    {
        $this->userTable = $userTable;
        $this->usageTable = $usageTable;
        $this->configService = $configService;
        $this->mailer = $mailer;
    }

    /**
     * Method which is called in case a user visits a route which cost a specific amount of points. This method
     * decreases the points from the user account
     */
    public function pay(int $points, ContextInterface $context): void
    {
        // decrease user points
        $this->userTable->payPoints($context->getUser()->getId(), $points);

        // add usage entry
        $record = new Table\Generated\PlanUsageRow([
            Table\Generated\PlanUsageTable::COLUMN_ROUTE_ID => $context->getRouteId(),
            Table\Generated\PlanUsageTable::COLUMN_USER_ID => $context->getUser()->getId(),
            Table\Generated\PlanUsageTable::COLUMN_APP_ID => $context->getApp()->getId(),
            Table\Generated\PlanUsageTable::COLUMN_POINTS => $points,
            Table\Generated\PlanUsageTable::COLUMN_INSERT_DATE => new \DateTime(),
        ]);

        $this->usageTable->create($record);

        // send mail in case the points crossed a specific threshold
        $threshold = $this->configService->getValue('points_threshold');
        if ($threshold > 0 && $this->hasCrossedThreshold($threshold, $context->getUser()->getPoints(), $points)) {
            $this->mailer->sendPointsThresholdMail(
                $context->getUser()->getName(),
                $context->getUser()->getEmail(),
                $context->getUser()->getPoints()
            );
        }
    }

    private function hasCrossedThreshold(int $threshold, int $points, int $costs): bool
    {
        $maxPoints = $points;
        $minPoints = $points - $costs;

        return $threshold > $minPoints && $threshold <= $maxPoints;
    }
}
