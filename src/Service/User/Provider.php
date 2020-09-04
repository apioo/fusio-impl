<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\User;

use Fusio\Engine\Model\User;
use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\User_Remote;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Service;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Provider
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    protected $appTokenService;

    /**
     * @var \Fusio\Impl\Provider\ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \Fusio\Impl\Provider\ProviderFactory $providerFactory
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Service\User $userService, Service\App\Token $appTokenService, ProviderFactory $providerFactory, Config $config)
    {
        $this->userService     = $userService;
        $this->appTokenService = $appTokenService;
        $this->providerFactory = $providerFactory;
        $this->config          = $config;
    }

    /**
     * @param string $providerName
     * @param string $code
     * @param string $clientId
     * @param string $redirectUri
     * @return string
     * @throws \Throwable
     */
    public function provider($providerName, $code, $clientId, $redirectUri)
    {
        /** @var ProviderInterface $provider */
        $provider = $this->providerFactory->factory($providerName);
        $user     = $provider->requestUser($code, $clientId, $redirectUri);

        if ($user instanceof User) {
            $scopes = $this->userService->getDefaultScopes();

            $remote = new User_Remote();
            $remote->setProvider($provider->getId());
            $remote->setRemoteId($user->getId());
            $remote->setName($user->getName());
            $remote->setEmail($user->getEmail());
            $remote->setScopes($scopes);

            $userId = $this->userService->createRemote($remote, UserContext::newAnonymousContext());

            // @TODO this is the consumer app. Probably we need a better way to
            // define this id
            $appId = 2;

            $token = $this->appTokenService->generateAccessToken(
                $appId,
                $userId,
                $scopes,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                new \DateInterval($this->config->get('fusio_expire_consumer'))
            );

            return $token->getAccessToken();
        } else {
            throw new StatusCode\BadRequestException('Could not request user information');
        }
    }
}
