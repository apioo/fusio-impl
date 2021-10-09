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

namespace Fusio\Impl\Provider\Push\Zip;

use GuzzleHttp\Client;
use PSX\Json\Parser;

/**
 * ZipUpload
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ZipUpload
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param \GuzzleHttp\Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client(['http_errors' => false]);
    }

    /**
     * @param string $file
     * @param string $providerHost
     * @param string $providerKey
     * @return \Generator
     */
    public function uploadZip($file, $providerHost, $providerKey)
    {
        if (!is_file($file)) {
            throw new \RuntimeException('Could not find file to upload');
        }

        $hash = hash_file('sha256', $file);
        $size = filesize($file);

        yield 'Discovering provider at ' . $providerHost;

        $provider = $this->discoverProvider($providerHost, $providerKey, $hash, $size);
        if (!$provider instanceof Provider) {
            throw new \RuntimeException('Could not discover provider');
        }

        yield 'Discovered instance for host ' . $provider->getHostname();

        $sender = new Sender($this->client);

        yield from $sender->send($file, $provider->getPushUrl(), $provider->getStatusUrl());
    }

    /**
     * @param string $providerHost
     * @param string $providerKey
     * @param string $hash
     * @param integer $size
     * @return \Fusio\Impl\Provider\Push\Zip\Provider
     */
    private function discoverProvider($providerHost, $providerKey, $hash, $size)
    {
        $url = 'http://deploy.' . $providerHost . '/.well-known/deploy';

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $providerKey,
                'Accept' => 'application/json'
            ],
            'json' => [
                'hash' => $hash,
                'size' => $size,
            ],
        ]);

        $body = (string) $response->getBody();
        $data = Parser::decode($body, true);

        if ($response->getStatusCode() == 200) {
            return Provider::fromArray($data);
        } elseif ($response->getStatusCode() == 429) {
            $retryAfter = $response->getHeaderLine('Retry-After') ?: 300;

            throw new \RuntimeException(sprintf('You have made too many deploys please try again after %s seconds', $retryAfter));
        } else {
            if ($data['message'] && is_string($data['message'])) {
                throw new \RuntimeException($data['message']);
            } else {
                throw new \RuntimeException(sprintf('Looks like %s is not valid Fusio cloud provider', $providerHost));
            }
        }
    }
}
