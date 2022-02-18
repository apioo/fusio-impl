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

namespace Fusio\Impl\Event\App;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;

/**
 * GeneratedTokenEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GeneratedTokenEvent extends EventAbstract
{
    private int $appId;
    private int $tokenId;
    private string $accessToken;
    private array $scopes;
    private \DateTimeInterface $expires;
    private \DateTimeInterface $now;

    public function __construct(int $appId, int $tokenId, string $accessToken, array $scopes, \DateTimeInterface $expires, \DateTimeInterface $now, UserContext $context)
    {
        parent::__construct($context);

        $this->appId       = $appId;
        $this->tokenId     = $tokenId;
        $this->accessToken = $accessToken;
        $this->scopes      = $scopes;
        $this->expires     = $expires;
        $this->now         = $now;
    }

    public function getAppId(): int
    {
        return $this->appId;
    }

    public function getTokenId(): int
    {
        return $this->tokenId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpires(): \DateTimeInterface
    {
        return $this->expires;
    }

    public function getNow(): \DateTimeInterface
    {
        return $this->now;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }
}
