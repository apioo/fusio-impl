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
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Sender
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Sender
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var boolean
     */
    private $sending;

    /**
     * @var boolean
     */
    private $upload;

    /**
     * @var integer
     */
    private $line;

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
        $this->sending = true;
        $this->upload  = false;
        $this->line    = 0;

        // upload zip
        $promise = $this->client->requestAsync('POST', $deployUrl, [
            'multipart' => [
                [
                    'name' => 'fusio',
                    'contents' => fopen($file, 'r'),
                ],
            ],
        ]);

        $promise->then(
            function (ResponseInterface $response) {
                $this->sending = false;
                $this->upload  = true;
            },
            function (RequestException $e) {
                $this->sending = false;
                $this->upload  = false;
            }
        );

        while (true) {
            if (!$this->sending) {
                if (!$this->upload) {
                    throw new \RuntimeException('An error occurred while uploading');
                }

                $response = $this->client->request('GET', $statusUrl, [
                    'headers' => [
                        'Range' => 'lines=' . $this->line . '-'
                    ],
                ]);

                if ($response->getStatusCode() == 206) {
                    // get content range
                    $range   = $response->getHeaderLine('Content-Range');
                    $matches = [];
                    preg_match('/lines (\d+)-(\d+)/', $range, $matches);

                    $line = $matches[2] ?? 0;
                    $this->line = intval($line);

                    yield $response->getBody()->getContents();
                } else {
                    // invalid status code
                    break;
                }
            } else {
                // currently uploading the zip ... wait
            }

            usleep(1000);
        }
    }
}
