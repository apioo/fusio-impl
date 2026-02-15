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

namespace Fusio\Impl\Service\Agent\Intent;

use Fusio\Impl\Service\Agent\IntentInterface;
use Fusio\Impl\Service\Agent\Serializer\ResultSerializer;
use Fusio\Model\Backend\AgentMessage;
use Symfony\AI\Platform\Result\ResultInterface;

/**
 * ActionIntent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ActionIntent implements IntentInterface
{
    public function __construct(private ResultSerializer $resultSerializer)
    {
    }

    public function getMessage(): string
    {
        $hint = 'The user has the intent to develop a new action.' . "\n";
        $hint.= 'Therefor you need to transform the provided business logic of the user message into PHP code.' . "\n";
        $hint.= 'The resulting PHP code must be wrapped into the following code template:' . "\n";
        $hint.= "\n";
        $hint.= '<template>' . "\n";
        $hint.= <<<PHP
Action: [NAME]
<?php

use Fusio\Worker;
use Fusio\Engine;
use Psr\Log\LoggerInterface;

return function(Worker\ExecuteRequest \$request, Worker\ExecuteContext \$context, Engine\ConnectorInterface \$connector, Engine\Response\FactoryInterface \$response, Engine\DispatcherInterface \$dispatcher, LoggerInterface \$logger) {

[CODE]

};

PHP;
        $hint.= '</template>' . "\n";
        $hint.= "\n";
        $hint.= 'Replace "[CODE]" with the code which you have generated.' . "\n";
        $hint.= 'Replace "[NAME]" with a short and precise name as lower case and separated by hyphens which summarizes the business logic of the user message.' . "\n";
        $hint.= "\n";
        $hint.= 'If the business logic wants to interact with an external service i.e. a database or remote HTTP endpoint, then you can use the getConnection method at the connector argument to access those external services.' . "\n";
        $hint.= 'You can get a list of all available connections through the "backend_connection_getAll" tool.' . "\n";
        $hint.= "\n";
        $hint.= 'Depending on the connection class the getConnection method returns a different instance, use the following mapping to better understand which implementation is used:';
        $hint.= '* Fusio.Adapter.Amqp.Connection.Amqp = AMQPStreamConnection from the php-amqplib library' . "\n";
        $hint.= '* Fusio.Adapter.Beanstalk.Connection.Beanstalk = Pheanstalk from the pheanstalk library' . "\n";
        $hint.= '* Fusio.Adapter.File.Connection.Filesystem = FilesystemOperator from the Flysystem library' . "\n";
        $hint.= '* Fusio.Adapter.Http.Connection.Http = Client from the Guzzle HTTP library' . "\n";
        $hint.= '* Fusio.Adapter.Redis.Connection.Redis = Client from the predis library' . "\n";
        $hint.= '* Fusio.Adapter.Smtp.Connection.Smtp = Mailer from the Symfony Mailer library' . "\n";
        $hint.= '* Fusio.Adapter.Soap.Connection.Soap = SoapClient from the SOAP PHP extension' . "\n";
        $hint.= '* Fusio.Impl.Connection.System = Connection from the Doctrine DBAL library' . "\n";
        $hint.= '* Fusio.Adapter.Sql.Connection.Sql = Connection from the Doctrine DBAL library' . "\n";
        $hint.= '* Fusio.Adapter.Sql.Connection.SqlAdvanced = Connection from the Doctrine DBAL library' . "\n";
        $hint.= '* Fusio.Adapter.Stripe.Connection.Stripe = StripeClient from the Stripe PHP SDK' . "\n";
        $hint.= "\n";
        $hint.= 'If the business logic needs a database and there is no specific connection mentioned then use as default the "System" connection.' . "\n";
        $hint.= 'If the business logic needs to work with a database table you can get all available tables for a specific connection through the "backend_database_getTables" tool where you need to provide a connection id.' . "\n";
        $hint.= 'If you need to get a concrete table schema you can use the "backend_database_getTable" tool where you need to provide the connection id and table name.' . "\n";
        $hint.= 'To add logging you can use the "$logger" argument which is a PSR-3 compatible logging interface.' . "\n";
        $hint.= "\n";
        $hint.= 'Methods which can be used inside an action:' . "\n";
        $hint.= '* Get uri fragment or query parameter: $request->getArguments()->get([name])' . "\n";
        $hint.= '* Get uri fragment or query parameter with a default value: $request->getArguments()->getOrDefault([name], [default])' . "\n";
        $hint.= '* Get request payload: $request->getPayload()' . "\n";
        $hint.= '* Get operation id: $context->getOperationId()' . "\n";
        $hint.= '* Get base url: $context->getBaseUrl()' . "\n";
        $hint.= '* Get user id: $context->getUser()->getId()' . "\n";
        $hint.= '* Get user name: $context->getUser()->getName()' . "\n";
        $hint.= '* Get user email: $context->getUser()->getEmail()' . "\n";
        $hint.= '* Get user points: $context->getUser()->getPoints()' . "\n";
        $hint.= '* Get connection: $connector->getConnection([connection_id])' . "\n";
        $hint.= '* Dispatch event: $dispatcher->dispatch([event_name], [payload])' . "\n";
        $hint.= '* Build response: $response->build([status_code], [headers], [body])' . "\n";
        $hint.= "\n";
        $hint.= 'The generated business logic must use the build method of the "$response" factory to return a result.' . "\n";
        $hint.= 'Normally you do not need to set a Content-Type header or use "json_encode" since the framework handles the Content-Type and serializes the provided body into JSON.' . "\n";
        $hint.= 'You only need to set a Content-Type and serialize the body if the user explicit wants to return a specific format like XML.' . "\n";
        $hint.= "\n";

        return $hint;
    }

    public function getTools(): array
    {
        return [
            'backend_action_getAll',
            'backend_action_get',
            'backend_action_getClasses',
            'backend_action_getForm',
            'backend_action_get',
            'backend_connection_getAll',
            'backend_connection_get',
            'backend_connection_database_getTables',
            'backend_connection_database_getTable',
            'backend_connection_filesystem_getAll',
            'backend_connection_filesystem_get',
            'backend_connection_http_execute',
            'backend_connection_sdk_get',
        ];
    }

    public function getResponseSchema(): ?array
    {
        return null;
    }

    public function transformResult(ResultInterface $result): AgentMessage
    {
        return $this->resultSerializer->serialize($result);
    }
}
