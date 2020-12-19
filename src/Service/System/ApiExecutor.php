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

namespace Fusio\Impl\Service\System;

use DateTime;
use Doctrine\DBAL\Connection;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Base;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use PSX\Framework\Dispatch\Dispatch;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\Stream\Stream;
use PSX\Json\Parser;
use PSX\Uri\Uri;

/**
 * ApiExecutor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ApiExecutor
{
    /**
     * @var \PSX\Framework\Dispatch\Dispatch
     */
    private $dispatch;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @param \PSX\Framework\Dispatch\Dispatch $dispatch
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Dispatch $dispatch, Connection $connection, LoggerInterface $logger)
    {
        $this->dispatch   = $dispatch;
        $this->connection = $connection;
        $this->logger     = $logger;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $body
     * @param boolean $verbose
     * @return mixed
     */
    public function request($method, $path, $body = null, $verbose = false)
    {
        $header   = ['User-Agent' => Base::getUserAgent(), 'Authorization' => 'Bearer ' . $this->getAccessToken()];
        $body     = $body !== null ? Parser::encode($body) : null;
        $request  = new Request(new Uri('/backend/' . $path), $method, $header, $body);
        $response = new Response();
        $response->setBody(new Stream(fopen('php://memory', 'r+')));

        if ($this->logger instanceof Logger) {
            $this->logger->pushHandler($verbose ? new StreamHandler(STDOUT) : new NullHandler());
        }

        $this->dispatch->route($request, $response);

        if ($this->logger instanceof Logger) {
            $this->logger->popHandler();
        }

        $body = (string) $response->getBody();
        $data = Parser::decode($body, false);

        return $data;
    }

    /**
     * @return string
     */
    protected function getAccessToken()
    {
        if (empty($this->accessToken)) {
            // insert access token
            $token  = TokenGenerator::generateToken();
            $expire = new DateTime('+30 minute');
            $now    = new DateTime();

            $this->connection->insert('fusio_app_token', [
                'app_id'  => 1,
                'user_id' => 1,
                'status'  => 1,
                'token'   => $token,
                'scope'   => 'backend',
                'ip'      => '127.0.0.1',
                'expire'  => $expire->format('Y-m-d H:i:s'),
                'date'    => $now->format('Y-m-d H:i:s'),
            ]);

            return $this->accessToken = $token;
        } else {
            return $this->accessToken;
        }
    }
}
