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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Service\User\Model\User as UserModel;
use PSX\Framework\Config\Config;
use PSX\Http\Client\ClientInterface;
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
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var \Fusio\Impl\Service\User\ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var \Fusio\Impl\Service\User\TokenIssuer
     */
    protected $tokenIssuer;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Fusio\Impl\Service\User\ProviderFactory $providerFactory
     * @param \Fusio\Impl\Service\User\TokenIssuer $tokenIssuer
     */
    public function __construct(Service\User $userService, Service\Config $configService, ProviderFactory $providerFactory, TokenIssuer $tokenIssuer)
    {
        $this->userService     = $userService;
        $this->configService   = $configService;
        $this->providerFactory = $providerFactory;
        $this->tokenIssuer     = $tokenIssuer;
    }

    public function provider($providerName, $code, $clientId, $redirectUri)
    {
        $providerName = strtolower($providerName);
        $provider     = $this->providerFactory->factory($providerName);

        if ($provider instanceof ProviderInterface) {
            $user = $provider->requestUser($code, $clientId, $redirectUri);

            if ($user instanceof UserModel) {
                $scopes = $this->getDefaultScopes();
                $userId = $this->userService->createRemote(
                    $provider->getId(),
                    $user->getId(),
                    $user->getName(),
                    $user->getEmail(),
                    $scopes,
                    UserContext::newAnonymousContext()
                );

                return $this->tokenIssuer->createToken($userId, $scopes);
            } else {
                throw new StatusCode\BadRequestException('Could not request user information');
            }
        } else {
            throw new StatusCode\BadRequestException('Not supported provider');
        }
    }

    protected function getDefaultScopes()
    {
        $scopes = $this->configService->getValue('scopes_default');

        return array_filter(array_map('trim', Service\Scope::split($scopes)), function ($scope) {
            // we filter out the backend scope since this would be a major
            // security issue
            return !empty($scope) && $scope != 'backend';
        });
    }
}
