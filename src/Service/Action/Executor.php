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

namespace Fusio\Impl\Service\Action;

use Fusio\Engine\Context;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Repository;
use Fusio\Engine\Request;
use Fusio\Impl\Backend\Model\Action_Execute_Request;
use Fusio\Impl\Table;
use PSX\Http\Environment\HttpContext;
use PSX\Http\Request as HttpRequest;
use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Uri\Uri;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Executor
{
    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @var \Fusio\Engine\ProcessorInterface
     */
    protected $processor;

    /**
     * @var \Fusio\Engine\Repository\AppInterface
     */
    protected $appRepository;

    /**
     * @var \Fusio\Engine\Repository\UserInterface
     */
    protected $userRepository;

    /**
     * @param \Fusio\Impl\Table\Action $actionTable
     * @param \Fusio\Engine\ProcessorInterface $processor
     * @param \Fusio\Engine\Repository\AppInterface $appRepository
     * @param \Fusio\Engine\Repository\UserInterface $userRepository
     */
    public function __construct(Table\Action $actionTable, ProcessorInterface $processor, Repository\AppInterface $appRepository, Repository\UserInterface $userRepository)
    {
        $this->actionTable       = $actionTable;
        $this->processor         = $processor;
        $this->appRepository     = $appRepository;
        $this->userRepository    = $userRepository;
    }

    public function execute($actionId, Action_Execute_Request $request)
    {
        $action = $this->actionTable->get($actionId);
        if (empty($action)) {
            return null;
        }

        $body = $request->getBody();
        if ($body === null) {
            $body = new Record();
        }

        $app  = $this->appRepository->get(1);
        $user = $this->userRepository->get(1);

        $uriFragments = $this->parseQueryString($request->getUriFragments());
        $parameters   = $this->parseQueryString($request->getParameters());
        $headers      = $this->parseQueryString($request->getHeaders());

        $uri = new Uri('/');
        $uri = $uri->withParameters($parameters);

        $httpRequest = new HttpRequest($uri, $request->getMethod(), $headers);
        $httpContext = new HttpContext($httpRequest, $uriFragments);

        $context = new Context($actionId, '/', $app, $user);
        $request = new Request($httpContext, $body);

        return $this->processor->execute($action['id'], $request, $context);
    }

    private function parseQueryString($data)
    {
        $result = array();
        if (!empty($data)) {
            parse_str($data, $result);
        }
        return $result;
    }
}
