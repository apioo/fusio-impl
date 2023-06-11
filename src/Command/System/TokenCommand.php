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

namespace Fusio\Impl\Command\System;

use DateInterval;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * TokenCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class TokenCommand extends Command
{
    private Service\App\Token $appTokenService;
    private Service\Scope $scopeService;
    private Table\App $appTable;
    private Table\User $userTable;

    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Table\App $appTable, Table\User $userTable)
    {
        parent::__construct();

        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->appTable        = $appTable;
        $this->userTable       = $userTable;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:token')
            ->setDescription('Generates a new access token')
            ->addArgument('appId', InputArgument::REQUIRED, 'Name or ID of the app')
            ->addArgument('userId', InputArgument::REQUIRED, 'Name or ID of the user')
            ->addArgument('scopes', InputArgument::REQUIRED, 'Comma separated list of scopes')
            ->addArgument('expire', InputArgument::REQUIRED, 'Interval when the token expires (i.e. P1D for one day)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app    = $this->findApp($input->getArgument('appId'));
        $user   = $this->findUser($input->getArgument('userId'));
        $scopes = $this->parseScopes($input->getArgument('scopes'), $app, $user);
        $expire = $this->parseExpire($input->getArgument('expire'));
        $ip     = '127.0.0.1';

        $accessToken = $this->appTokenService->generateAccessToken($app->getId(), $user->getId(), $scopes, $ip, $expire);

        $response = [
            'App'   => $app->getName(),
            'User'  => $user->getName(),
            'Token' => $accessToken->getAccessToken(),
        ];

        $expiresIn = $accessToken->getExpiresIn();
        if (isset($expiresIn)) {
            $response['Expires'] = date('Y-m-d', $expiresIn);
        }

        $scope = $accessToken->getScope();
        if (isset($scope)) {
            $response['Scope'] = $scope;
        }

        $output->writeln("");
        $output->writeln(Yaml::dump($response, 2));
        $output->writeln("");

        return self::SUCCESS;
    }

    private function findApp(mixed $appId): Table\Generated\AppRow
    {
        if (!is_numeric($appId)) {
            $app = $this->appTable->findOneByName($appId);
        } else {
            $app = $this->appTable->find((int) $appId);
        }

        if (!$app instanceof Table\Generated\AppRow) {
            throw new RuntimeException('Invalid app');
        }

        return $app;
    }

    private function findUser(mixed $userId): Table\Generated\UserRow
    {
        if (!is_numeric($userId)) {
            $user = $this->userTable->findOneByName($userId);
        } else {
            $user = $this->userTable->find((int) $userId);
        }

        if (!$user instanceof Table\Generated\UserRow) {
            throw new RuntimeException('Invalid user');
        }

        return $user;
    }

    private function parseScopes(mixed $scopes, Table\Generated\AppRow $app, Table\Generated\UserRow $user): array
    {
        return $this->scopeService->getValidScopes((string) $scopes, $app->getId(), $user->getId());
    }

    private function parseExpire(mixed $expire): DateInterval
    {
        return new DateInterval((string) $expire);
    }
}
