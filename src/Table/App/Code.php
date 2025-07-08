<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Table\App;

use Fusio\Impl\Table\App;
use Fusio\Impl\Table\Generated;
use PSX\Sql\Condition;

/**
 * Code
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Code extends Generated\AppCodeTable
{
    public function getCodeByRequest(string $appKey, string $appSecret, string $code, ?string $redirectUri, ?string $tenantId = null): array|false
    {
        $condition = Condition::withAnd();
        $condition->equals('app.' . Generated\AppTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals('app.' . Generated\AppTable::COLUMN_STATUS, App::STATUS_ACTIVE);
        $condition->equals('app.' . Generated\AppTable::COLUMN_APP_KEY, $appKey);
        $condition->equals('app.' . Generated\AppTable::COLUMN_APP_SECRET, $appSecret);
        $condition->equals('app_code.' . self::COLUMN_CODE, $code);
        $condition->equals('app_code.' . self::COLUMN_REDIRECT_URI, $redirectUri ?: '');

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'app_code.' . self::COLUMN_ID,
                'app_code.' . self::COLUMN_APP_ID,
                'app_code.' . self::COLUMN_USER_ID,
                'app_code.' . self::COLUMN_SCOPE,
                'app_code.' . self::COLUMN_DATE,
            ])
            ->from('fusio_app_code', 'app_code')
            ->innerJoin('app_code', 'fusio_app', 'app', 'app_code.' . self::COLUMN_APP_ID . ' = app.' . Generated\AppTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }
}
