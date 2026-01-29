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

namespace Fusio\Impl\Service\Agent\Serializer;

use Fusio\Adapter\Worker\Action\WorkerPHPLocal;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Model\Backend\AgentMessage;
use Fusio\Model\Backend\AgentMessageObject;
use InvalidArgumentException;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

/**
 * ActionResultSerializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ActionResultSerializer extends ResultSerializer
{
    public function serialize(ResultInterface $result): AgentMessage
    {
        if (!$result instanceof TextResult) {
            throw new InvalidArgumentException('Expect a text result got: ' . $result::class);
        }

        $content = $result->getContent();

        // try to extract the action name
        preg_match('/\* Name: ([A-Za-z0-9-]+)/im', $content, $matches);

        $name = $matches[1] ?? null;
        if (empty($name)) {
            $name = 'Action-' . substr(sha1($content), 0, 8);
        }

        $object = new AgentMessageObject();
        $object->setType('object');
        $object->setPayload([
            'name' => $name,
            'class' => ClassName::serialize(WorkerPHPLocal::class),
            'config' => [
                'code' => $content,
            ],
        ]);

        return $object;
    }
}
