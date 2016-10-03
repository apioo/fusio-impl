<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use DateInterval;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Table;
use PSX\DateTime\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Oauth2\AccessToken;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;

/**
 * App
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class App
{
    /**
     * @var \Fusio\Impl\Table\App
     */
    protected $appTable;

    /**
     * @var \Fusio\Impl\Table\Scope
     */
    protected $scopeTable;

    /**
     * @var \Fusio\Impl\Table\App\Scope
     */
    protected $appScopeTable;

    /**
     * @var \Fusio\Impl\Table\App\Token
     */
    protected $appTokenTable;

    /**
     * @var string
     */
    protected $tokenSecret;

    public function __construct(Table\App $appTable, Table\Scope $scopeTable, Table\App\Scope $appScopeTable, Table\App\Token $appTokenTable, $tokenSecret)
    {
        $this->appTable       = $appTable;
        $this->scopeTable     = $scopeTable;
        $this->appScopeTable  = $appScopeTable;
        $this->appTokenTable  = $appTokenTable;
        $this->tokenSecret    = $tokenSecret;
    }

    public function getAll($startIndex = 0, $search = null)
    {
        $condition = new Condition();
        $condition->in('status', [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING]);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        return new ResultSet(
            $this->appTable->getCount($condition),
            $startIndex,
            16,
            $this->appTable->getAll(
                $startIndex,
                16,
                'id',
                Sql::SORT_DESC,
                $condition,
                Fields::blacklist(['url', 'parameters', 'appSecret'])
            )
        );
    }

    public function get($appId)
    {
        $app = $this->appTable->get($appId);

        if (!empty($app)) {
            if ($app['status'] == Table\App::STATUS_DELETED) {
                throw new StatusCode\GoneException('App was deleted');
            }

            $app['scopes'] = $this->scopeTable->getByApp($app['id']);
            $app['tokens'] = $this->appTokenTable->getTokensByApp($app['id']);

            return $app;
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    public function getPublic($appKey, $scope)
    {
        $condition = new Condition();
        $condition->equals('appKey', $appKey);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->getOneBy($condition, Fields::blacklist(['userId', 'status', 'parameters', 'appKey', 'appSecret', 'date']));

        if (!empty($app)) {
            $app['scopes'] = $this->appScopeTable->getByApp($app['id'], $scope, ['backend']);

            return $app;
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    public function getByAppKey($appKey)
    {
        $condition = new Condition();
        $condition->equals('appKey', $appKey);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        return $this->appTable->getOneBy($condition);
    }

    public function getByAppKeyAndSecret($appKey, $appSecret)
    {
        $condition = new Condition();
        $condition->equals('appKey', $appKey);
        $condition->equals('appSecret', $appSecret);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        return $this->appTable->getOneBy($condition);
    }

    public function create($userId, $status, $name, $url, $parameters = null, array $scopes = null)
    {
        // check whether app exists
        $condition  = new Condition();
        $condition->equals('userId', $userId);
        $condition->notEquals('status', Table\App::STATUS_DELETED);
        $condition->equals('name', $name);

        $app = $this->appTable->getOneBy($condition);

        if (!empty($app)) {
            throw new StatusCode\BadRequestException('App already exists');
        }

        // parse parameters
        if ($parameters !== null) {
            $parameters = $this->parseParameters($parameters);
        }

        // create app
        $appKey    = TokenGenerator::generateAppKey();
        $appSecret = TokenGenerator::generateAppSecret();

        try {
            $this->appTable->beginTransaction();

            $this->appTable->create(array(
                'userId'     => $userId,
                'status'     => $status,
                'name'       => $name,
                'url'        => $url,
                'parameters' => $parameters,
                'appKey'     => $appKey,
                'appSecret'  => $appSecret,
                'date'       => new DateTime(),
            ));

            $appId = $this->appTable->getLastInsertId();

            if ($scopes !== null) {
                // insert scopes
                $this->insertScopes($appId, $scopes);
            }

            $this->appTable->commit();
        } catch (\Exception $e) {
            $this->appTable->rollBack();

            throw $e;
        }
    }

    public function update($appId, $status, $name, $url, $parameters = null, array $scopes = null)
    {
        $app = $this->appTable->get($appId);

        if (!empty($app)) {
            if ($app['status'] == Table\App::STATUS_DELETED) {
                throw new StatusCode\GoneException('App was deleted');
            }

            // parse parameters
            if ($parameters !== null) {
                $parameters = $this->parseParameters($parameters);
            } else {
                $parameters = $app['parameters'];
            }

            try {
                $this->appTable->beginTransaction();

                $this->appTable->update(array(
                    'id'         => $app['id'],
                    'status'     => $status,
                    'name'       => $name,
                    'url'        => $url,
                    'parameters' => $parameters,
                ));

                if ($scopes !== null) {
                    // delete existing scopes
                    $this->appScopeTable->deleteAllFromApp($app['id']);

                    // insert scopes
                    $this->insertScopes($app['id'], $scopes);
                }

                $this->appTable->commit();
            } catch (\Exception $e) {
                $this->appTable->rollBack();

                throw $e;
            }
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    public function delete($appId)
    {
        $app = $this->appTable->get($appId);

        if (!empty($app)) {
            if ($app['status'] == Table\App::STATUS_DELETED) {
                throw new StatusCode\GoneException('App was deleted');
            }

            $this->appTable->update(array(
                'id'     => $app['id'],
                'status' => Table\App::STATUS_DELETED,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    public function removeToken($appId, $tokenId)
    {
        $app = $this->appTable->get($appId);

        if (!empty($app)) {
            $this->appTokenTable->removeTokenFromApp($appId, $tokenId);
        } else {
            throw new StatusCode\NotFoundException('Invalid app');
        }
    }

    public function generateAccessToken($appId, $userId, array $scopes, $ip, DateInterval $expire)
    {
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No scopes provided');
        }

        $expires = new \DateTime();
        $expires->add($expire);
        $now     = new \DateTime();

        // generate access token
        $accessToken = TokenGenerator::generateToken();

        $this->appTokenTable->create([
            'appId'  => $appId,
            'userId' => $userId,
            'status' => Table\App\Token::STATUS_ACTIVE,
            'token'  => $accessToken,
            'scope'  => implode(',', $scopes),
            'ip'     => $ip,
            'expire' => $expires,
            'date'   => $now,
        ]);

        $token = new AccessToken();
        $token->setAccessToken($accessToken);
        $token->setTokenType('bearer');
        $token->setExpiresIn($expires->getTimestamp());
        $token->setScope(implode(',', $scopes));

        return $token;
    }

    public function insertScopes($appId, $scopes)
    {
        if (!empty($scopes) && is_array($scopes)) {
            $scopes = $this->scopeTable->getByNames($scopes);

            foreach ($scopes as $scope) {
                $this->appScopeTable->create(array(
                    'appId'   => $appId,
                    'scopeId' => $scope['id'],
                ));
            }
        }
    }

    protected function parseParameters($parameters)
    {
        parse_str($parameters, $data);

        $params = [];
        foreach ($data as $key => $value) {
            if (!ctype_alnum($key)) {
                throw new StatusCode\BadRequestException('Invalid parameter key only alnum characters are allowed');
            }
            if (!preg_match('/^[\x21-\x7E]*$/', $value)) {
                throw new StatusCode\BadRequestException('Invalid parameter value only printable ascii characters are allowed');
            }
            $params[$key] = $value;
        }

        return http_build_query($params, '', '&');
    }
}
