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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenCommand extends Command
{
    private Service\Token $tokenService;
    private Service\Scope $scopeService;
    private Table\App $appTable;
    private Table\User $userTable;

    public function __construct(Service\Token $tokenService, Service\Scope $scopeService, Table\App $appTable, Table\User $userTable)
    {
        parent::__construct();

        $this->tokenService = $tokenService;
        $this->scopeService = $scopeService;
        $this->appTable = $appTable;
        $this->userTable = $userTable;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:token')
            ->setDescription('Generates a new access token')
            ->addArgument('appId', InputArgument::REQUIRED, 'Name or ID of the app')
            ->addArgument('userId', InputArgument::REQUIRED, 'Name or ID of the user')
            ->addArgument('scopes', InputArgument::REQUIRED, 'Comma separated list of scopes')
            ->addArgument('expire', InputArgument::REQUIRED, 'Interval when the token expires (i.e. P1D for one day)')
            ->addArgument('tenantId', InputArgument::OPTIONAL, 'The tenant id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantId = $input->getArgument('tenantId');
        $app = $this->findApp($input->getArgument('appId'));
        $user = $this->findUser($input->getArgument('userId'));
        $scopes = $this->parseScopes($tenantId, $input->getArgument('scopes'), $app, $user);
        $expire = $this->parseExpire($input->getArgument('expire'));
        $ip = '127.0.0.1';
        $name = 'CLI';

        $accessToken = $this->tokenService->generate(
            $tenantId,
            Table\Category::TYPE_SYSTEM,
            $app->getId(),
            $user->getId(),
            $name,
            $scopes,
            $ip,
            $expire
        );

        $response = [
            'App'   => $app->getName(),
            'User'  => $user->getName(),
            'Token' => $accessToken->getAccessToken(),
        ];

        $expiresIn = $accessToken->getExpiresIn();
        if (isset($expiresIn)) {
            $response['Expires'] = $expiresIn;
        }

        $scope = $accessToken->getScope();
        if (isset($scope)) {
            $response['Scope'] = $scope;
        }

        $output->writeln("");
        $output->writeln(Yaml::dump($response, 4));
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

    private function parseScopes(?string $tenantId, mixed $scopes, Table\Generated\AppRow $app, Table\Generated\UserRow $user): array
    {
        return $this->scopeService->getValidScopes($tenantId, (string) $scopes, $app->getId(), $user->getId());
    }

    private function parseExpire(mixed $expire): DateInterval
    {
        return new DateInterval((string) $expire);
    }
}
