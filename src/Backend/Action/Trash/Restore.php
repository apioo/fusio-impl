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

namespace Fusio\Impl\Backend\Action\Trash;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\Restorer;
use Fusio\Model\Backend\TrashRestore;
use PSX\Http\Exception as StatusCode;

/**
 * Restore
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Restore extends ActionAbstract
{
    private Restorer $restorer;

    public function __construct(RuntimeInterface $runtime, Restorer $restorer)
    {
        parent::__construct($runtime);

        $this->restorer = $restorer;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof TrashRestore);

        $id = $body->getId();
        if ($id === null) {
            throw new StatusCode\BadRequestException('No id to restore provided');
        }

        $this->restorer->restore(
            $request->get('type'),
            (string) $id
        );

        return [
            'success' => true,
            'message' => 'Restore successful',
        ];
    }
}
