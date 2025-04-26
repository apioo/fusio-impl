<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;

/**
 * Cleaner
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Cleaner
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function cleanUp(): void
    {
        $this->cleanUpExpiredTokens();
        $this->cleanUpIdentityRequests();
        $this->cleanUpWebhookResponses();
        $this->cleanUpCronjobErrors();
        $this->cleanUpLogErrors();
        $this->cleanUpFirewallExpired();
        $this->cleanUpFirewallLogs();
    }

    private function cleanUpExpiredTokens(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_token WHERE expire < :now', [
            'now' => (new \DateTime('first day of last month'))->format('Y-m-d H:i:s'),
        ]);
    }

    private function cleanUpIdentityRequests(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_identity_request WHERE insert_date < :now', [
            'now' => (new \DateTime('first day of last month'))->format('Y-m-d H:i:s'),
        ]);
    }

    private function cleanUpWebhookResponses(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_webhook_response WHERE insert_date < :now', [
            'now' => (new \DateTime('first day of last month'))->format('Y-m-d H:i:s'),
        ]);
    }

    private function cleanUpCronjobErrors(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_cronjob_error WHERE insert_date < :now', [
            'now' => (new \DateTime('first day of -3 months'))->format('Y-m-d H:i:s'),
        ]);
    }

    private function cleanUpLogErrors(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_log_error WHERE insert_date < :now', [
            'now' => (new \DateTime('first day of -3 months'))->format('Y-m-d H:i:s'),
        ]);
    }

    private function cleanUpFirewallExpired(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_firewall WHERE expire < :now', [
            'now' => (new \DateTime('first day of last month'))->format('Y-m-d H:i:s'),
        ]);
    }

    private function cleanUpFirewallLogs(): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_firewall_log WHERE insert_date < :now', [
            'now' => (new \DateTime('first day of -3 months'))->format('Y-m-d H:i:s'),
        ]);
    }
}
