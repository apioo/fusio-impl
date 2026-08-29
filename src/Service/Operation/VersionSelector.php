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

namespace Fusio\Impl\Service\Operation;

use Fusio\Impl\Action\Scheme as ActionScheme;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Framework\Schema\Scheme as SchemaScheme;
use Fusio\Impl\Table;

/**
 * VersionSelector
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class VersionSelector
{
    public function __construct(
        private Table\Action $actionTable,
        private Table\Action\Tag $actionTagTable,
        private Table\Schema $schemaTable,
        private Table\Schema\Tag $schemaTagTable,
    ) {
    }

    public function select(string $version, Table\Generated\OperationRow $operation, Context $context): void
    {
        $this->selectAction($version, $operation, $context);
        $this->selectIncomingSchema($version, $operation, $context);
        $this->selectOutgoingSchema($version, $operation, $context);
    }

    private function selectAction(string $version, Table\Generated\OperationRow $operation, Context $context): void
    {
        [$scheme, $name] = ActionScheme::split($operation->getAction());

        if ($scheme !== ActionScheme::ACTION) {
            return;
        }

        $action = $this->actionTable->findOneByTenantAndName($context->getTenantId(), null, $name);
        if ($action instanceof Table\Generated\ActionRow) {
            $hash = $this->actionTagTable->findHashByVersion($version, $action->getId());
            if (!empty($hash)) {
                $operation->setAction($scheme->value . '://' . $name . '@' . $hash);
            }
        }
    }

    private function selectIncomingSchema(string $version, Table\Generated\OperationRow $operation, Context $context): void
    {
        $incoming = $operation->getIncoming();
        if (empty($incoming)) {
            return;
        }

        $uri = $this->buildSchemaUri($incoming, $version, $operation, $context);
        if (!empty($uri)) {
            $operation->setIncoming($uri);
        }
    }

    private function selectOutgoingSchema(string $version, Table\Generated\OperationRow $operation, Context $context): void
    {
        $uri = $this->buildSchemaUri($operation->getOutgoing(), $version, $operation, $context);
        if (!empty($uri)) {
            $operation->setOutgoing($uri);
        }
    }

    private function buildSchemaUri(string $uri, string $version, Table\Generated\OperationRow $operation, Context $context): ?string
    {
        [$scheme, $name] = SchemaScheme::split($uri);

        if ($scheme !== SchemaScheme::SCHEMA) {
            return null;
        }

        $schema = $this->schemaTable->findOneByTenantAndName($context->getTenantId(), null, $name);
        if ($schema instanceof Table\Generated\SchemaRow) {
            $hash = $this->schemaTagTable->findHashByVersion($version, $schema->getId());
            if (!empty($hash)) {
                return $scheme->value . '://' . $name . '@' . $hash;
            }
        }

        return null;
    }
}
