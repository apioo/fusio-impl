<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Backend\Action\Action;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Provider\ActionProvider;
use Fusio\Impl\Service\Marketplace;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Service\TypeSystem\KindBuilder;
use Fusio\Marketplace\MarketplaceAction;
use Fusio\Marketplace\MarketplaceMessageException;
use Fusio\Marketplace\MarketplaceUser;
use Fusio\Model;
use PSX\Http\Exception as StatusCode;
use Sdkgen\Client\Exception\ClientException;

/**
 * GetIndex
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GetIndex implements ActionInterface
{
    public function __construct(
        private ActionProvider $actionParser,
        private FrameworkConfig $frameworkConfig,
        private Marketplace\Action\Repository $marketplaceRepository,
    ) {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $marketplace = (bool) $request->get('marketplace');
        $startIndex = (int) $request->get('startIndex');
        $search = $request->get('search');

        if ($marketplace) {
            try {
                $result = $this->marketplaceRepository->fetchAll($startIndex, $search);
            } catch (MarketplaceMessageException $e) {
                $message = $e->getPayload()->getMessage();

                throw new StatusCode\InternalServerErrorException('Could not fetch actions from marketplace: ' . $message, previous: $e);
            } catch (ClientException $e) {
                throw new StatusCode\InternalServerErrorException('Could not fetch actions from marketplace: ' . $e->getMessage(), previous: $e);
            }

            $actions = [];
            $objects = $result->getEntry() ?? [];
            foreach ($objects as $object) {
                if (!$object instanceof MarketplaceAction) {
                    continue;
                }

                $author = $object->getAuthor();
                if (!$author instanceof MarketplaceUser) {
                    continue;
                }

                $actions[] = [
                    'name' => $author->getName() . ' / ' . $object->getName(),
                    'description' => $object->getDescription(),
                    'class' => $author->getName() . '/' . $object->getName(),
                ];
            }
        } else {
            $actions = $this->actionParser->getClasses($this->frameworkConfig->getActionExclude());
        }

        return [
            'kind' => KindBuilder::build(Model\Backend\ActionIndex::class),
            'actions' => $actions,
        ];
    }
}
