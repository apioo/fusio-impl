<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Console\System;

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

    protected function configure()
    {
        $this
            ->setName('system:token')
            ->setDescription('Generates a new access token')
            ->addArgument('appId', InputArgument::REQUIRED, 'Name or ID of the app')
            ->addArgument('userId', InputArgument::REQUIRED, 'Name or ID of the user')
            ->addArgument('scopes', InputArgument::REQUIRED, 'Comma separated list of scopes')
            ->addArgument('expire', InputArgument::REQUIRED, 'Interval when the token expires (i.e. P1D for one day)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appId  = $input->getArgument('appId');
        $userId = $input->getArgument('userId');
        $scopes = $input->getArgument('scopes');
        $expire = $input->getArgument('expire');

        if (!is_numeric($appId)) {
            $app = $this->appTable->findOneByName($appId);
        } else {
            $app = $this->appTable->find($appId);
        }

        if (empty($app)) {
            throw new RuntimeException('Invalid app');
        }

        if (!is_numeric($userId)) {
            $user = $this->userTable->findOneByName($userId);
        } else {
            $user = $this->userTable->find($userId);
        }

        if (empty($user)) {
            throw new RuntimeException('Invalid user');
        }

        $scopes = $this->scopeService->getValidScopes($scopes, (int) $app['id'], (int) $user['id']);
        $ip     = '127.0.0.1';
        $expire = new DateInterval($expire);

        $accessToken = $this->appTokenService->generateAccessToken($app['id'], $user['id'], $scopes, $ip, $expire);

        $response = [
            'App'     => $app['name'],
            'User'    => $user['name'],
            'Token'   => $accessToken->getAccessToken(),
            'Expires' => date('Y-m-d', $accessToken->getExpiresIn()),
            'Scope'   => $accessToken->getScope(),
        ];

        $output->writeln("");
        $output->writeln(Yaml::dump($response, 2));
        $output->writeln("");

        return 0;
    }
}
