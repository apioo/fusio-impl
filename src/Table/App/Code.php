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

namespace Fusio\Impl\Table\App;

use Fusio\Impl\Table\App;
use Fusio\Impl\Table\Generated;

/**
 * Code
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Code extends Generated\AppCodeTable
{
    public function getCodeByRequest($appKey, $appSecret, $code, $redirectUri)
    {
        $sql = '    SELECT code.id,
                           code.app_id,
                           code.user_id,
                           code.scope,
                           code.date
                      FROM fusio_app_code code
                INNER JOIN fusio_app app
                        ON app.id = code.app_id
                     WHERE app.app_key = :app_key
                       AND app.app_secret = :app_secret
                       AND app.status = :status
                       AND code.code = :code
                       AND code.redirect_uri = :redirect_uri';

        return $this->connection->fetchAssociative($sql, array(
            'app_key'      => $appKey,
            'app_secret'   => $appSecret,
            'status'       => App::STATUS_ACTIVE,
            'code'         => $code,
            'redirect_uri' => $redirectUri ?: '',
        ));
    }
}
