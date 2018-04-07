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

namespace Fusio\Impl\Service\System\Push;

use GuzzleHttp\Client;
use PSX\Json\Parser;

/**
 * Sender
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Sender
{
    const COMPLETED_LINE = '----5ac7cda93bcaf';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $file
     * @param string $deployUrl
     * @param string $statusUrl
     * @return \Generator
     */
    public function send($file, $deployUrl, $statusUrl)
    {
        yield 'Uploading ' . $file;

        // upload zip
        $response = $this->client->request('POST', $deployUrl, [
            'multipart' => [
                [
                    'name' => 'fusio',
                    'contents' => fopen($file, 'r'),
                    'headers'  => ['Content-Type' => 'application/zip']
                ],
            ],
        ]);

        if ($response->getStatusCode() >= 400) {
            $body = (string) $response->getBody();
            $data = Parser::decode($body, true);

            $message = $data['message'] ?? 'An error occurred while uploading, received status code ' . $response->getStatusCode();

            throw new \RuntimeException($message);
        }

        yield 'Upload completed execute deployment on remote instance';

        // request status during deployment
        $line    = -1;
        $pending = true;

        while ($pending) {
            $response = $this->client->request('GET', $statusUrl);

            $body = (string) $response->getBody();
            $data = Parser::decode($body, true);

            if ($response->getStatusCode() >= 400) {
                $message = $data['message'] ?? 'Received an error status code ' . $response->getStatusCode();

                throw new \RuntimeException($message);
            }

            if (isset($data['logs']) && is_array($data['logs'])) {
                foreach ($data['logs'] as $index => $message) {
                    // check for completed line
                    if ($message == self::COMPLETED_LINE) {
                        $pending = false;
                        break;
                    }

                    // we yield only new lines
                    if ($index > $line) {
                        yield $message;
                        $line = $index;
                    }
                }
            } else {
                throw new \RuntimeException('Received invalid data');
            }

            usleep(1500);
        }
    }
}
