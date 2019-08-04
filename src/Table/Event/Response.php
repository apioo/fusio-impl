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

namespace Fusio\Impl\Table\Event;

use Fusio\Impl\Table;
use PSX\Sql\TableAbstract;

/**
 * Response
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Response extends TableAbstract
{
    const STATUS_PENDING = 1;
    const STATUS_DONE = 2;
    const STATUS_EXCEEDED = 3;

    const RESPONSE_LIMIT = 60;

    public function getName()
    {
        return 'fusio_event_response';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'trigger_id' => self::TYPE_INT,
            'subscription_id' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'code' => self::TYPE_INT,
            'attempts' => self::TYPE_INT,
            'execute_date' => self::TYPE_DATETIME,
            'insert_date' => self::TYPE_DATETIME,
        );
    }
    
    public function getAllPending()
    {
        $sql = 'SELECT response.id,
                       response.attempts,
                       subscription.endpoint,
                       trigg.payload
                  FROM fusio_event_response response
            INNER JOIN fusio_event_subscription subscription
                    ON subscription.id = response.subscription_id
            INNER JOIN fusio_event_trigger trigg
                    ON trigg.id = response.trigger_id
                 WHERE response.status = :status
              ORDER BY trigg.insert_date ASC, response.id ASC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, self::RESPONSE_LIMIT);

        return $this->connection->fetchAll($sql, [
            'status' => Table\Event\Response::STATUS_PENDING
        ]);
    }

    public function getAllBySubscription($userId, $subscriptionId)
    {
        $sql = 'SELECT response.id,
                       response.status,
                       response.code,
                       response.attempts,
                       response.execute_date
                  FROM fusio_event_response response
            INNER JOIN fusio_event_subscription subscription
                    ON subscription.id = response.subscription_id
                 WHERE subscription.id = :id
                   AND subscription.user_id = :user_id
              ORDER BY response.execute_date DESC, response.id ASC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 8);

        return $this->connection->fetchAll($sql, [
            'id'      => $subscriptionId,
            'user_id' => $userId,
        ]);
    }
    
    public function setResponse($responseId, $code, $attempts, $maxAttempts)
    {
        $now      = new \DateTime();
        $attempts = $attempts + 1;

        if (($code >= 200 && $code < 400) || $code == 410) {
            $status = self::STATUS_DONE;
        } else {
            $status = self::STATUS_PENDING;
        }

        // mark response as exceeded in case max attempts is reached
        if ($attempts >= $maxAttempts) {
            $status = self::STATUS_EXCEEDED;
        }

        $sql = 'UPDATE fusio_event_response
                   SET status = :status,
                       code = :code,
                       attempts = :attempts,
                       execute_date = :now
                 WHERE id = :id';

        return $this->connection->executeUpdate($sql, [
            'id'       => $responseId,
            'status'   => $status,
            'code'     => $code,
            'attempts' => $attempts,
            'now'      => $now->format('Y-m-d H:i:s'),
        ]);
    }
}
