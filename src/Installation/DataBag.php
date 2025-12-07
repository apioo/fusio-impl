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

namespace Fusio\Impl\Installation;

use Fusio\Adapter;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Table;
use PSX\Schema\ContentType;
use PSX\Schema\TypeInterface;

/**
 * DataBag
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DataBag
{
    private array $data;

    public function __construct()
    {
        $this->data = [
            'fusio_category' => [],
            'fusio_role' => [],
            'fusio_plan' => [],
            'fusio_user' => [],
            'fusio_action' => [],
            'fusio_app' => [],
            'fusio_audit' => [],
            'fusio_config' => [],
            'fusio_operation' => [],
            'fusio_connection' => [],
            'fusio_cronjob' => [],
            'fusio_event' => [],
            'fusio_log' => [],
            'fusio_provider' => [],
            'fusio_page' => [],
            'fusio_rate' => [],
            'fusio_schema' => [],
            'fusio_scope' => [],
            'fusio_transaction' => [],
            'fusio_app_code' => [],
            'fusio_app_scope' => [],
            'fusio_token' => [],
            'fusio_test' => [],
            'fusio_firewall' => [],
            'fusio_form' => [],
            'fusio_bundle' => [],
            'fusio_cronjob_error' => [],
            'fusio_webhook' => [],
            'fusio_webhook_response' => [],
            'fusio_log_error' => [],
            'fusio_plan_usage' => [],
            'fusio_rate_allocation' => [],
            'fusio_scope_operation' => [],
            'fusio_user_grant' => [],
            'fusio_user_scope' => [],
            'fusio_user_attribute' => [],
            'fusio_role_scope' => [],
        ];
    }

    public function addOperations(?string $tenantId, string $category, array $operations): void
    {
        $this->addCategory($category, tenantId: $tenantId);
        $this->addScope($category, $category, tenantId: $tenantId);

        foreach ($operations as $name => $operation) {
            /** @var Operation $operation */
            if ($category !== 'default') {
                $path = '/' . $category . $operation->httpPath;
                $operationName = $category . '.' . $name;
            } else {
                $path = $operation->httpPath;
                $operationName = $name;
            }

            if (class_exists($operation->action)) {
                $action = 'php+class://' . ClassName::serialize($operation->action);
            } else {
                $action = 'action://' . $operation->action;
            }

            $incoming = null;
            if (isset($operation->incoming)) {
                $incoming = $this->normalizeSchema($operation->incoming);
            }

            $outgoing = $this->normalizeSchema($operation->outgoing);

            $this->addOperation(
                $category,
                $operation->public,
                $operation->stability,
                $operationName,
                $operation->httpMethod,
                $path,
                $operation->httpCode,
                $this->normalizeParameters($operation->parameters),
                $incoming,
                $outgoing,
                $this->normalizeThrows($operation->throws),
                $action,
                $operation->costs,
                tenantId: $tenantId,
                description: $operation->description,
            );

            if (in_array($category, ['backend', 'consumer'])) {
                $parts = explode('.', $name);
                $scope = $category . '.' . $parts[0];
                $this->addScope($category, $scope, tenantId: $tenantId);
                $this->addScopeOperation($scope, $operationName, tenantId: $tenantId);

                if (isset($parts[1]) && in_array($parts[1], ['create', 'update', 'delete'])) {
                    $eventName = 'fusio.' . $parts[0] . '.' . $parts[1];
                    $this->addEvent($category, $eventName, tenantId: $tenantId);
                }
            } elseif ($category === 'authorization') {
                $scope = $category;
                $this->addScope($category, $scope, tenantId: $tenantId);
                $this->addScopeOperation($scope, $operationName, tenantId: $tenantId);
            }

            if (!empty($operation->eventName)) {
                $this->addEvent($category, $operation->eventName, tenantId: $tenantId);
            }
        }
    }

    private function normalizeSchema(string $schema): string
    {
        if (class_exists($schema)) {
            return 'php+class://' . ClassName::serialize($schema);
        } elseif (in_array($schema, [ContentType::BINARY, ContentType::FORM, ContentType::JSON, ContentType::MULTIPART, ContentType::TEXT, ContentType::XML])) {
            return 'mime://' . $schema;
        } else {
            return 'schema://' . $schema;
        }
    }

    private function normalizeParameters(array $parameters): object
    {
        $result = new \stdClass();
        foreach ($parameters as $name => $type) {
            /** @var TypeInterface $type */
            $result->{$name} = $type->toArray();
        }

        return $result;
    }

    private function normalizeThrows(array $throws): object
    {
        $result = new \stdClass();
        foreach ($throws as $code => $class) {
            if (class_exists($class)) {
                $throw = 'php+class://' . ClassName::serialize($class);
            } else {
                $throw = 'schema://' . $class;
            }

            $result->{$code} = $throw;
        }

        return $result;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->data as $key => $value) {
            $result[$key] = array_values($value);
        }

        return $result;
    }

    public function addAction(string $category, string $name, string $class, ?string $config = null, ?array $metadata = null, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_action'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Action::STATUS_ACTIVE,
            'name' => $name,
            'class' => ClassName::serialize($class),
            'config' => $config,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addApp(string $user, string $name, string $url, string $appKey, string $appSecret, int $status = Table\App::STATUS_ACTIVE, ?array $metadata = null, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_app'][$name] = [
            'tenant_id' => $tenantId,
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'status' => $status,
            'name' => $name,
            'url' => $url,
            'parameters' => '',
            'app_key' => $appKey,
            'app_secret' => $appSecret,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addAppCode(string $app, string $user, string $code, string $scope, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_app_code'][$code] = [
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'code' => $code,
            'redirect_uri' => '',
            'scope' => $scope,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addAppScope(string $app, string $scope, ?string $tenantId = null): void
    {
        $this->data['fusio_app_scope'][] = [
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'scope_id' => $this->getReference('fusio_scope', $scope, $tenantId),
        ];
    }

    public function addAudit(string $app, string $user, int $ref, string $event, string $message, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_audit'][] = [
            'tenant_id' => $tenantId,
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'ref_id' => $ref,
            'event' => $event,
            'ip' => '127.0.0.1',
            'message' => $message,
            'content' => '{"foo": "bar"}',
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addBundle(string $name, string $version, string $icon, string $summary, string $description, array $config, int $cost, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_bundle'][$name] = [
            'tenant_id' => $tenantId,
            'status' => Table\Bundle::STATUS_ACTIVE,
            'name' => $name,
            'version' => $version,
            'icon' => $icon,
            'summary' => $summary,
            'description' => $description,
            'cost' => $cost,
            'config' => json_encode($config),
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addCategory(string $category, ?string $tenantId = null): void
    {
        $this->data['fusio_category'][$category] = [
            'tenant_id' => $tenantId,
            'status' => Table\Category::STATUS_ACTIVE,
            'name' => $category,
        ];
    }

    public function addConfig(string $name, int $type, $value, string $description, ?string $tenantId = null): void
    {
        $this->data['fusio_config'][$name] = [
            'tenant_id' => $tenantId,
            'name' => $name,
            'type' => $type,
            'description' => $description,
            'value' => $value
        ];
    }

    public function addConnection(string $name, string $class, ?string $config = null, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_connection'][$name] = [
            'tenant_id' => $tenantId,
            'status' => Table\Connection::STATUS_ACTIVE,
            'name' => $name,
            'class' => ClassName::serialize($class),
            'config' => $config,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addCronjob(string $category, string $name, string $cron, string $action, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_cronjob'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Cronjob::STATUS_ACTIVE,
            'name' => $name,
            'cron' => $cron,
            'action' => $action,
            'execute_date' => '2015-02-27 19:59:15',
            'exit_code' => 0,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addCronjobError(string $cronjob, string $message, ?string $tenantId = null, ?string $insertDate = null): void
    {
        $this->data['fusio_cronjob_error'][] = [
            'cronjob_id' => $this->getReference('fusio_cronjob', $cronjob, $tenantId),
            'message' => $message,
            'trace' => '[trace]',
            'file' => '[file]',
            'line' => 74,
            'insert_date' => (new \DateTime($insertDate ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addEvent(string $category, string $name, string $description = '', ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_event'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Event::STATUS_ACTIVE,
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addFirewall(string $name, string $ip, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_firewall'][$name] = [
            'tenant_id' => $tenantId,
            'status' => Table\Firewall::STATUS_ACTIVE,
            'name' => $name,
            'type' => Table\Firewall::TYPE_DENY,
            'ip' => $ip,
            'expire' => null,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addForm(string $name, string $operation, array $uiSchema, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_form'][$name] = [
            'tenant_id' => $tenantId,
            'status' => Table\Form::STATUS_ACTIVE,
            'name' => $name,
            'operation_id' => $this->getReference('fusio_operation', $operation, $tenantId),
            'ui_schema' => json_encode($uiSchema),
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addIdentity(string $app, string $name, string $icon, string $class, string $clientId, string $clientSecret, string $authorizationUri, string $tokenUri, string $userInfoUri, string $idProperty = 'id', string $nameProperty = 'name', string $emailProperty = 'email', ?string $insertDate = null, ?string $tenantId = null): void
    {
        $this->data['fusio_identity'][$name] = [
            'tenant_id' => $tenantId,
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'role_id' => $this->getReference('fusio_role', 'Consumer', $tenantId),
            'status' => Table\Identity::STATUS_ACTIVE,
            'name' => $name,
            'icon' => $icon,
            'class' => $class,
            'config' => \json_encode([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'authorization_uri' => $authorizationUri,
                'token_uri' => $tokenUri,
                'user_info_uri' => $userInfoUri,
                'id_property' => $idProperty,
                'name_property' => $nameProperty,
                'email_property' => $emailProperty,
            ]),
            'allow_create' => true,
            'insert_date' => (new \DateTime($insertDate ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addIdentityRequest(string $identity, string $state, ?string $insertDate = null, ?string $tenantId = null): void
    {
        $this->data['fusio_identity_request'][] = [
            'identity_id' => $this->getReference('fusio_identity', $identity, $tenantId),
            'state' => $state,
            'redirect_uri' => 'http://127.0.0.1/my/app',
            'insert_date' => (new \DateTime($insertDate ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addWebhook(string $event, string $user, string $name, string $endpoint, ?string $tenantId = null): void
    {
        $this->data['fusio_webhook'][$name] = [
            'tenant_id' => $tenantId,
            'event_id' => $this->getReference('fusio_event', $event, $tenantId),
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'status' => 1,
            'name' => $name,
            'endpoint' => $endpoint
        ];
    }

    public function addWebhookResponse(int $webhookId, ?string $executeDate = null, ?string $insertDate = null): void
    {
        $this->data['fusio_webhook_response'][] = [
            'webhook_id' => $webhookId,
            'status' => 2,
            'code' => 200,
            'attempts' => 1,
            'execute_date' => (new \DateTime($executeDate ?? 'now'))->format('Y-m-d H:i:s'),
            'insert_date' => (new \DateTime($insertDate ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addLog(string $category, ?string $app, string $user, string $operation, ?string $tenantId = null): void
    {
        $this->data['fusio_log'][] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'app_id' => $app !== null ? $this->getReference('fusio_app', $app, $tenantId) : null,
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'operation_id' => $this->getReference('fusio_operation', $operation, $tenantId),
            'ip' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'method' => 'GET',
            'path' => '/bar',
            'header' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'body' => 'foobar',
            'execution_time' => 500000,
            'date' => '2015-06-25 22:49:09'
        ];
    }

    public function addLogError(int $log, ?string $insertDate = null): void
    {
        $this->data['fusio_log_error'][] = [
            'log_id' => $log,
            'message' => 'Syntax error, malformed JSON',
            'trace' => '[trace]',
            'file' => '[file]',
            'line' => 74,
            'insert_date' => (new \DateTime($insertDate ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addPage(string $title, string $slug, string $content, int $status = Table\Page::STATUS_VISIBLE, ?array $metadata = null, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_page'][$slug] = [
            'tenant_id' => $tenantId,
            'status' => $status,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addPlan(string $name, float $price, int $points, ?int $period, ?string $externalId = null, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_plan'][$name] = [
            'tenant_id' => $tenantId,
            'status' => Table\Plan::STATUS_ACTIVE,
            'name' => $name,
            'description' => '',
            'price' => $price,
            'points' => $points,
            'period_type' => $period,
            'external_id' => $externalId,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addPlanUsage(string $operation, string $user, string $app, int $points, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_plan_usage'][] = [
            'operation_id' => $this->getReference('fusio_operation', $operation, $tenantId),
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'points' => $points,
            'insert_date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addPlanScope(string $plan, string $scope, ?string $tenantId = null): void
    {
        $this->data['fusio_plan_scope'][] = [
            'plan_id' => $this->getReference('fusio_plan', $plan, $tenantId),
            'scope_id' => $this->getReference('fusio_scope', $scope, $tenantId),
        ];
    }

    public function addToken(string $app, string $user, string $name, string $token, string $refresh, string $scope, string $expire, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_token'][] = [
            'tenant_id' => $tenantId,
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'status' => Table\Token::STATUS_ACTIVE,
            'name' => $name,
            'token' => $token,
            'refresh' => $refresh,
            'scope' => $scope,
            'ip' => '127.0.0.1',
            'expire' => (new \DateTime($expire))->format('Y-m-d H:i:s'),
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addTransaction(string $user, string $plan, int $amount, string $periodStart, string $periodEnd, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_transaction'][] = [
            'tenant_id' => $tenantId,
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'plan_id' => $this->getReference('fusio_plan', $plan, $tenantId),
            'transaction_id' => '[transaction_id]',
            'amount' => $amount,
            'points' => 1000,
            'period_start' => (new \DateTime($periodStart))->format('Y-m-d H:i:s'),
            'period_end' => (new \DateTime($periodEnd))->format('Y-m-d H:i:s'),
            'insert_date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addRate(string $name, int $priority, int $rateLimit, string $timespan, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_rate'][$name] = [
            'tenant_id' => $tenantId,
            'status' => Table\Rate::STATUS_ACTIVE,
            'priority' => $priority,
            'name' => $name,
            'rate_limit' => $rateLimit,
            'timespan' => $timespan,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addRateAllocation(string $rate, ?string $operation = null, ?string $user = null, ?string $plan = null, ?string $app = null, ?bool $authenticated = null, ?string $tenantId = null): void
    {
        $this->data['fusio_rate_allocation'][] = [
            'rate_id' => $this->getReference('fusio_rate', $rate, $tenantId),
            'operation_id' => $operation !== null ? $this->getReference('fusio_operation', $operation, $tenantId) : null,
            'user_id' => $user !== null ? $this->getReference('fusio_user', $user, $tenantId) : null,
            'plan_id' => $plan !== null ? $this->getReference('fusio_plan', $plan, $tenantId) : null,
            'app_id' => $app !== null ? $this->getReference('fusio_app', $app, $tenantId) : null,
            'authenticated' => $authenticated !== null ? ($authenticated ? 1 : 0) : null,
        ];
    }

    public function addRole(string $category, string $name, ?string $tenantId = null): void
    {
        $this->data['fusio_role'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Role::STATUS_ACTIVE,
            'name' => $name,
        ];
    }

    public function addRoleScope(string $role, string $scope, ?string $tenantId = null): void
    {
        $this->data['fusio_role_scope'][$role . $scope] = [
            'role_id' => $this->getReference('fusio_role', $role, $tenantId),
            'scope_id' => $this->getReference('fusio_scope', $scope, $tenantId),
        ];
    }

    public function addOperation(string $category, bool $public, int $stability, string $name, string $httpMethod, string $httpPath, int $httpCode, object $parameters, ?string $incoming, ?string $outgoing, object $throws, string $action, ?int $costs = null, ?array $metadata = null, ?string $tenantId = null, ?string $description = null): void
    {
        $this->data['fusio_operation'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Operation::STATUS_ACTIVE,
            'active' => 1,
            'public' => $public ? 1 : 0,
            'stability' => $stability,
            'description' => $description ?? '',
            'http_method' => $httpMethod,
            'http_path' => $httpPath,
            'http_code' => $httpCode,
            'name' => $name,
            'parameters' => \json_encode($parameters),
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'throws' => \json_encode($throws),
            'action' => $action,
            'costs' => $costs ?? 0,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addSchema(string $category, string $name, string $source, ?string $form = null, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_schema'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Schema::STATUS_ACTIVE,
            'name' => $name,
            'source' => $source,
            'form' => $form,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addScope(string $category, string $name, string $description = '', ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_scope'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addScopeOperation(string $scope, string $operation, ?string $tenantId = null): void
    {
        $this->data['fusio_scope_operation'][$scope . $operation] = [
            'scope_id' => $this->getReference('fusio_scope', $scope, $tenantId),
            'operation_id' => $this->getReference('fusio_operation', $operation, $tenantId),
            'allow' => 1
        ];
    }

    public function addTest(string $category, string $operation, ?string $tenantId = null): void
    {
        $this->data['fusio_test'][$category . $operation] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'operation_id' => $this->getReference('fusio_operation', $operation, $tenantId),
            'status' => Table\Schema::STATUS_ACTIVE,
            'message' => 'message',
            'response' => 'response',
            'uri_fragments' => 'foo=bar',
            'parameters' => 'foo=bar',
            'headers' => 'foo=bar',
            'body' => '{"foo": "bar"}',
        ];
    }

    public function addTrigger(string $category, string $name, string $event, string $action, ?array $metadata = null, ?string $tenantId = null): void
    {
        $this->data['fusio_trigger'][$name] = [
            'tenant_id' => $tenantId,
            'category_id' => $this->getReference('fusio_category', $category, $tenantId),
            'status' => Table\Trigger::STATUS_ACTIVE,
            'name' => $name,
            'event' => $event,
            'action' => $action,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addUser(string $role, string $name, string $email, string $password, ?int $points = null, int $status = Table\User::STATUS_ACTIVE, ?string $plan = null, ?array $metadata = null, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_user'][$name] = [
            'tenant_id' => $tenantId,
            'role_id' => $this->getReference('fusio_role', $role, $tenantId),
            'plan_id' => $plan !== null ? $this->getReference('fusio_plan', $plan, $tenantId) : null,
            'status' => $status,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'points' => $points,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addUserScope(string $user, string $scope, ?string $tenantId = null): void
    {
        $this->data['fusio_user_scope'][] = [
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'scope_id' => $this->getReference('fusio_scope', $scope, $tenantId),
        ];
    }

    public function addUserGrant(string $user, string $app, bool $allow, ?string $date = null, ?string $tenantId = null): void
    {
        $this->data['fusio_user_grant'][] = [
            'user_id' => $this->getReference('fusio_user', $user, $tenantId),
            'app_id' => $this->getReference('fusio_app', $app, $tenantId),
            'allow' => $allow,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addTable(string $table, array $rows): void
    {
        if (isset($this->data[$table])) {
            throw new \RuntimeException('Table ' . $table . ' already exists');
        }

        $this->data[$table] = $rows;
    }

    public function getReference(string $type, string $name, ?string $tenantId): Reference
    {
        if (!isset($this->data[$type])) {
            throw new \RuntimeException('Provided an invalid type ' . $type);
        }

        return new Reference($type, $name, $tenantId);
    }

    public function replace(string $type, $name, $key, $value): void
    {
        if (isset($this->data[$type][$name][$key])) {
            $this->data[$type][$name][$key] = $value;
        }
    }

    public function getData(string $table, ?string $column = null, mixed $value = null): array
    {
        $data = $this->data[$table] ?? throw new \InvalidArgumentException('Provided table ' . $table . ' does not exist');

        if ($column !== null && $value !== null) {
            $data = array_filter($data, function(array $row) use ($column, $value) {
                return $row[$column] === $value;
            });
        }

        return $data;
    }
}
