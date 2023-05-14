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

namespace Fusio\Impl\Installation;

use Fusio\Adapter;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Controller\ActionController;
use Fusio\Impl\Table;
use PSX\Api\OperationInterface;
use PSX\Schema\TypeInterface;

/**
 * DataBag
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class DataBag
{
    private array $data;

    public function __construct()
    {
        $this->data = [
            'fusio_user' => [],
            'fusio_action' => [],
            'fusio_app' => [],
            'fusio_audit' => [],
            'fusio_config' => [],
            'fusio_category' => [],
            'fusio_connection' => [],
            'fusio_cronjob' => [],
            'fusio_event' => [],
            'fusio_log' => [],
            'fusio_plan' => [],
            'fusio_provider' => [],
            'fusio_page' => [],
            'fusio_role' => [],
            'fusio_rate' => [],
            'fusio_operation' => [],
            'fusio_schema' => [],
            'fusio_scope' => [],
            'fusio_transaction' => [],
            'fusio_app_code' => [],
            'fusio_app_scope' => [],
            'fusio_app_token' => [],
            'fusio_cronjob_error' => [],
            'fusio_event_subscription' => [],
            'fusio_event_trigger' => [],
            'fusio_event_response' => [],
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

    public function addOperations(string $category, array $operations): void
    {
        $this->addCategory($category);
        $this->addScope($category, $category);

        foreach ($operations as $name => $operation) {
            /** @var Operation $operation */
            if ($category !== 'default') {
                $path = '/' . $category . $operation->httpPath;
                $operationName = $category . '.' . $name;
            } else {
                $path = $operation->httpPath;
                $operationName = $name;
            }

            $actionName = $this->getActionName($operation->action);
            if (!$this->hasId('fusio_action', $actionName)) {
                $this->addAction($category, $actionName, $operation->action);
            }

            $incomingName = null;
            if (isset($operation->incoming)) {
                $incomingName = $this->getSchemaName($operation->incoming);
                if (!$this->hasId('fusio_schema', $incomingName)) {
                    $this->addSchema($category, $incomingName, $operation->incoming);
                }
            }

            $outgoingName = null;
            if (isset($operation->outgoing)) {
                $outgoingName = $this->getSchemaName($operation->outgoing);
                if (!$this->hasId('fusio_schema', $outgoingName)) {
                    $this->addSchema($category, $outgoingName, $operation->outgoing);
                }
            }

            $this->addOperation(
                $category,
                $operationName,
                $operation->httpMethod,
                $path,
                $this->normalizeParameters($operation->parameters),
                $incomingName,
                $outgoingName,
                $this->normalizeThrows($operation->throws, $category),
                $actionName
            );

            if (in_array($category, ['backend', 'consumer'])) {
                $parts = explode('.', $name);
                $scope = $category . '.' . $parts[0];
                $this->addScope($category, $scope);
                $this->addScopeOperation($scope, $operationName);

                if (in_array($parts[1], ['create', 'update', 'delete'])) {
                    $eventName = 'fusio.' . $parts[0] . '.' . $parts[1];
                    $this->addEvent($category, $eventName);
                }
            }
        }
    }

    private function normalizeParameters(array $parameters): array
    {
        $result = [];
        foreach ($parameters as $name => $type) {
            /** @var TypeInterface $type */
            $result[$name] = $type->toArray();
        }

        return $result;
    }

    private function normalizeThrows(array $throws, string $category): array
    {
        $result = [];
        foreach ($throws as $code => $class) {
            $schemaName = $this->getSchemaName($class);

            if (!$this->hasId('fusio_schema', $schemaName)) {
                $this->addSchema($category, $schemaName, $class);
            }

            $result[$code] = $schemaName;
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

    public function addAction(string $category, string $name, string $class, ?string $config = null, ?array $metadata = null, ?string $date = null): void
    {
        $this->data['fusio_action'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'status' => Table\Action::STATUS_ACTIVE,
            'name' => $name,
            'class' => $class,
            'engine' => PhpClass::class,
            'config' => $config,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addApp(string $user, string $name, string $url, string $appKey, string $appSecret, int $status = Table\App::STATUS_ACTIVE, ?array $metadata = null, ?string $date = null): void
    {
        $this->data['fusio_app'][$name] = [
            'user_id' => $this->getId('fusio_user', $user),
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

    public function addAppCode(string $app, string $user, string $code, string $scope, ?string $date = null): void
    {
        $this->data['fusio_app_code'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'user_id' => $this->getId('fusio_user', $user),
            'code' => $code,
            'redirect_uri' => '',
            'scope' => $scope,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addAppScope(string $app, string $scope): void
    {
        $this->data['fusio_app_scope'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'scope_id' => $this->getId('fusio_scope', $scope),
        ];
    }

    public function addAppToken(string $app, string $user, string $token, string $refresh, string $scope, string $expire, ?string $date = null): void
    {
        $this->data['fusio_app_token'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'user_id' => $this->getId('fusio_user', $user),
            'status' => Table\App\Token::STATUS_ACTIVE,
            'token' => $token,
            'refresh' => $refresh,
            'scope' => $scope,
            'ip' => '127.0.0.1',
            'expire' => (new \DateTime($expire))->format('Y-m-d H:i:s'),
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addAudit(string $app, string $user, int $ref, string $event, string $message, ?string $date = null): void
    {
        $this->data['fusio_audit'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'user_id' => $this->getId('fusio_user', $user),
            'ref_id' => $ref,
            'event' => $event,
            'ip' => '127.0.0.1',
            'message' => $message,
            'content' => '{"foo": "bar"}',
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addCategory(string $category): void
    {
        $this->data['fusio_category'][$category] = [
            'status' => Table\Category::STATUS_ACTIVE,
            'name' => $category,
        ];
    }

    public function addConfig(string $name, int $type, $value, string $description): void
    {
        $this->data['fusio_config'][$name] = [
            'name' => $name,
            'type' => $type,
            'description' => $description,
            'value' => $value
        ];
    }

    public function addConnection(string $name, string $class, ?string $config = null, ?array $metadata = null): void
    {
        $this->data['fusio_connection'][$name] = [
            'status' => Table\Connection::STATUS_ACTIVE,
            'name' => $name,
            'class' => $class,
            'config' => $config,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addCronjob(string $category, string $name, string $cron, string $action, ?array $metadata = null): void
    {
        $this->data['fusio_cronjob'][$name] = [
            'category_id' => $this->getId('fusio_category', $category),
            'status' => Table\Cronjob::STATUS_ACTIVE,
            'name' => $name,
            'cron' => $cron,
            'action' => $action,
            'execute_date' => '2015-02-27 19:59:15',
            'exit_code' => 0,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addCronjobError(string $cronjob, string $message): void
    {
        $this->data['fusio_cronjob_error'][] = [
            'cronjob_id' => $this->getId('fusio_cronjob', $cronjob),
            'message' => $message,
            'trace' => '[trace]',
            'file' => '[file]',
            'line' => 74
        ];
    }

    public function addEvent(string $category, string $name, string $description = '', ?array $metadata = null): void
    {
        $this->data['fusio_event'][$name] = [
            'category_id' => $this->getId('fusio_category', $category),
            'status' => Table\Event::STATUS_ACTIVE,
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addEventResponse(int $trigger, int $subscription, ?string $executeDate = null, ?string $insertDate = null): void
    {
        $this->data['fusio_event_response'][] = [
            'trigger_id' => $this->getId('fusio_event_trigger', $trigger),
            'subscription_id' => $this->getId('fusio_event_subscription', $subscription),
            'status' => 2,
            'code' => 200,
            'attempts' => 1,
            'execute_date' => (new \DateTime($executeDate ?? 'now'))->format('Y-m-d H:i:s'),
            'insert_date' => (new \DateTime($insertDate ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addEventSubscription(string $event, string $user, string $endpoint): void
    {
        $this->data['fusio_event_subscription'][] = [
            'event_id' => $this->getId('fusio_event', $event),
            'user_id' => $this->getId('fusio_user', $user),
            'status' => 1,
            'endpoint' => $endpoint
        ];
    }

    public function addEventTrigger(string $event, string $payload, ?string $date = null): void
    {
        $this->data['fusio_event_trigger'][] = [
            'event_id' => $this->getId('fusio_event', $event),
            'status' => 2,
            'payload' => $payload,
            'insert_date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addLog(string $category, string $app, string $operation): void
    {
        $this->data['fusio_log'][] = [
            'category_id' => $this->getId('fusio_category', $category),
            'app_id' => $this->getId('fusio_app', $app),
            'operation_id' => $this->getId('fusio_operation', $operation),
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

    public function addLogError(int $log): void
    {
        $this->data['fusio_log_error'][] = [
            'log_id' => $this->getId('fusio_log', $log),
            'message' => 'Syntax error, malformed JSON',
            'trace' => '[trace]',
            'file' => '[file]',
            'line' => 74
        ];
    }

    public function addPage(string $title, string $slug, string $content, int $status = Table\Page::STATUS_VISIBLE, ?array $metadata = null, ?string $date = null): void
    {
        $this->data['fusio_page'][$slug] = [
            'status' => $status,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addPlan(string $name, float $price, int $points, ?int $period, ?string $externalId = null, ?array $metadata = null): void
    {
        $this->data['fusio_plan'][$name] = [
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

    public function addPlanUsage(string $operation, string $user, string $app, int $points, ?string $date = null): void
    {
        $this->data['fusio_plan_usage'][] = [
            'operation_id' => $this->getId('fusio_operation', $operation),
            'user_id' => $this->getId('fusio_user', $user),
            'app_id' => $this->getId('fusio_app', $app),
            'points' => $points,
            'insert_date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addPlanScope(string $plan, string $scope): void
    {
        $this->data['fusio_plan_scope'][] = [
            'plan_id' => $this->getId('fusio_plan', $plan),
            'scope_id' => $this->getId('fusio_scope', $scope),
        ];
    }

    public function addTransaction(string $user, string $plan, int $amount, string $periodStart, string $periodEnd, ?string $date = null): void
    {
        $this->data['fusio_transaction'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'plan_id' => $this->getId('fusio_plan', $plan),
            'transaction_id' => '[transaction_id]',
            'amount' => $amount,
            'points' => 1000,
            'period_start' => (new \DateTime($periodStart))->format('Y-m-d H:i:s'),
            'period_end' => (new \DateTime($periodEnd))->format('Y-m-d H:i:s'),
            'insert_date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addRate(string $name, int $priority, int $rateLimit, string $timespan, ?array $metadata = null): void
    {
        $this->data['fusio_rate'][$name] = [
            'status' => Table\Rate::STATUS_ACTIVE,
            'priority' => $priority,
            'name' => $name,
            'rate_limit' => $rateLimit,
            'timespan' => $timespan,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addRateAllocation(string $rate, ?string $operation = null, ?string $user = null, ?string $plan = null, ?string $app = null, ?bool $authenticated = null): void
    {
        $this->data['fusio_rate_allocation'][] = [
            'rate_id' => $this->getId('fusio_rate', $rate),
            'operation_id' => $operation !== null ? $this->getId('fusio_operation', $operation) : null,
            'user_id' => $user !== null ? $this->getId('fusio_user', $user) : null,
            'plan_id' => $plan !== null ? $this->getId('fusio_plan', $plan) : null,
            'app_id' => $app !== null ? $this->getId('fusio_app', $app) : null,
            'authenticated' => $authenticated !== null ? ($authenticated ? 1 : 0) : null,
        ];
    }

    public function addRole(string $category, string $name): void
    {
        $this->data['fusio_role'][$name] = [
            'category_id' => $this->getId('fusio_category', $category),
            'status' => Table\Role::STATUS_ACTIVE,
            'name' => $name,
        ];
    }

    public function addRoleScope(string $role, string $scope): void
    {
        $this->data['fusio_role_scope'][$role . $scope] = [
            'role_id' => $this->getId('fusio_role', $role),
            'scope_id' => $this->getId('fusio_scope', $scope),
        ];
    }

    public function addOperation(string $category, string $name, string $httpMethod, string $httpPath, array $parameters, ?string $incoming, ?string $outgoing, array $throws, string $action, ?array $metadata = null): void
    {
        $this->data['fusio_operation'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'status' => Table\Operation::STATUS_ACTIVE,
            'active' => 1,
            'public' => 0,
            'stability' => OperationInterface::STABILITY_STABLE,
            'description' => '',
            'http_method' => $httpMethod,
            'http_path' => $httpPath,
            'name' => $name,
            'parameters' => \json_encode($parameters),
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'throws' => \json_encode($throws),
            'action' => $action,
            'costs' => 0,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addSchema(string $category, string $name, string $source, ?string $form = null, ?array $metadata = null): void
    {
        $this->data['fusio_schema'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'status' => Table\Schema::STATUS_ACTIVE,
            'name' => $name,
            'source' => $source,
            'form' => $form,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addScope(string $category, string $name, string $description = '', ?array $metadata = null): void
    {
        $this->data['fusio_scope'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
        ];
    }

    public function addScopeOperation(string $scope, string $operation): void
    {
        $this->data['fusio_scope_operation'][$scope . $operation] = [
            'scope_id' => self::getId('fusio_scope', $scope),
            'operation_id' => self::getId('fusio_operation', $operation),
            'allow' => 1,
            'methods' => 'GET|POST|PUT|PATCH|DELETE'
        ];
    }

    public function addUser(string $role, string $name, string $email, string $password, ?int $points = null, int $status = Table\User::STATUS_ACTIVE, ?string $plan = null, ?array $metadata = null, ?string $date = null): void
    {
        $this->data['fusio_user'][$name] = [
            'role_id' => self::getId('fusio_role', $role),
            'plan_id' => $plan !== null ? self::getId('fusio_plan', $plan) : null,
            'status' => $status,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'points' => $points,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'date' => (new \DateTime($date ?? 'now'))->format('Y-m-d H:i:s'),
        ];
    }

    public function addUserScope(string $user, string $scope): void
    {
        $this->data['fusio_user_scope'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'scope_id' => $this->getId('fusio_scope', $scope),
        ];
    }

    public function addUserGrant(string $user, string $app, bool $allow, ?string $date = null): void
    {
        $this->data['fusio_user_grant'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'app_id' => $this->getId('fusio_app', $app),
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

    public function getId(string $type, $name): int
    {
        if (!isset($this->data[$type])) {
            throw new \RuntimeException('Provided an invalid type ' . $type);
        }

        $index = 1;
        foreach ($this->data[$type] as $key => $value) {
            if ($name === $key) {
                return $index;
            }
            $index++;
        }

        throw new \RuntimeException('Could not find name ' . $name . ' for type ' . $type);
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

    private function hasId(string $type, $name): bool
    {
        try {
            $this->getId($type, $name);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    private function getActionName(string $class): string
    {
        $parts = explode('\\', $class);
        array_shift($parts); // Fusio
        array_shift($parts); // Impl
        return implode('_', $parts);
    }

    private function getSchemaName(string $class): string
    {
        $parts = explode('\\', $class);
        array_shift($parts);
        array_shift($parts);
        return implode('_', $parts);
    }
}