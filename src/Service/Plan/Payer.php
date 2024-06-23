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

namespace Fusio\Impl\Service\Plan;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception\PaymentRequiredException;

/**
 * Payer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
     * Method to check whether a user has enough points to pay the provided points. If this is true you can safely
     * call the pay method otherwise the points will go into the negative
     */
    public function canSpent(int $points, ContextInterface $context): bool
    {
        return $context->getUser()->getPoints() - $points >= 0;
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
        $row = new Table\Generated\PlanUsageRow();
        $row->setOperationId($context->getOperationId());
        $row->setUserId($context->getUser()->getId());
        $row->setAppId($context->getApp()->getId());
        $row->setPoints($points);
        $row->setInsertDate(LocalDateTime::now());
        $this->usageTable->create($row);

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
