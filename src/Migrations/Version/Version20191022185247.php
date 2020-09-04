<?php declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Migrations\MigrationUtil;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191022185247 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // add new scopes
        $count = $this->connection->fetchColumn('SELECT COUNT(*) AS cnt FROM fusio_scope WHERE name = :name', ['name' => 'backend.account']);
        if (empty($count)) {
            $this->insertScopes();
        }

        // add token column
        $userTable = $schema->getTable('fusio_user');
        if (!$userTable->hasColumn('token')) {
            $userTable->addColumn('token', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
        }

        // add period type column
        $planTable = $schema->getTable('fusio_plan');
        if (!$planTable->hasColumn('period_type')) {
            $planTable->addColumn('period_type', 'integer', ['notnull' => false]);
        }

        $planContractTable = $schema->getTable('fusio_plan_contract');
        if (!$planContractTable->hasColumn('period_type')) {
            $planContractTable->addColumn('period_type', 'integer', ['notnull' => false]);
        }

        // change config length
        $configTable = $schema->getTable('fusio_config');
        $configTable->changeColumn('value', ['length' => 512]);
    }

    public function postUp(Schema $schema): void
    {
    }

    public function down(Schema $schema) : void
    {
    }

    private function insertScopes()
    {
        $scopes = [
            ['name' => 'backend.account', 'description' => 'Option to change the password of your account'],
            ['name' => 'backend.action', 'description' => 'View and manage actions'],
            ['name' => 'backend.app', 'description' => 'View and manage apps'],
            ['name' => 'backend.audit', 'description' => 'View audits'],
            ['name' => 'backend.config', 'description' => 'View and edit config entries'],
            ['name' => 'backend.connection', 'description' => 'View and manage connections'],
            ['name' => 'backend.cronjob', 'description' => 'View and manage cronjob entries'],
            ['name' => 'backend.dashboard', 'description' => 'View dashboard statistic'],
            ['name' => 'backend.event', 'description' => 'View and manage events'],
            ['name' => 'backend.import', 'description' => 'Execute import'],
            ['name' => 'backend.log', 'description' => 'View logs'],
            ['name' => 'backend.marketplace', 'description' => 'View and manage apps from the marketplace'],
            ['name' => 'backend.plan', 'description' => 'View and manage plans'],
            ['name' => 'backend.rate', 'description' => 'View and manage rates'],
            ['name' => 'backend.routes', 'description' => 'View and manage routes'],
            ['name' => 'backend.schema', 'description' => 'View and manage schemas'],
            ['name' => 'backend.scope', 'description' => 'View and manage scopes'],
            ['name' => 'backend.sdk', 'description' => 'Generate client SDKs'],
            ['name' => 'backend.statistic', 'description' => 'View statistics'],
            ['name' => 'backend.transaction', 'description' => 'View transactions'],
            ['name' => 'backend.user', 'description' => 'View and manage users'],
            ['name' => 'consumer.app', 'description' => 'View and manage your apps'],
            ['name' => 'consumer.event', 'description' => 'View and manage your events'],
            ['name' => 'consumer.grant', 'description' => 'View and manage your grants'],
            ['name' => 'consumer.plan', 'description' => 'View available plans'],
            ['name' => 'consumer.scope', 'description' => 'View available scopes'],
            ['name' => 'consumer.subscription', 'description' => 'View and manage your subscriptions'],
            ['name' => 'consumer.transaction', 'description' => 'Execute transactions'],
            ['name' => 'consumer.user', 'description' => 'Edit your account settings'],
        ];

        foreach ($scopes as $scope) {
            $this->addSql('INSERT INTO fusio_scope (name, description) VALUES (:name, :description)', ['name' => $scope['name'], 'description' => $scope['description']]);
        }

        // assign scopes to routes
        foreach (['backend', 'consumer'] as $type) {
            $result = $this->connection->fetchAll('SELECT id, path FROM fusio_routes WHERE path LIKE :path', ['path' => '/' . $type . '/%']);
            foreach ($result as $row) {
                $parts = array_values(array_filter(explode('/', $row['path'])));
                if (count($parts) > 1) {
                    $name  = $parts[0] . '.' . $parts[1];
                    $scope = $this->connection->fetchAssoc('SELECT id FROM fusio_scope WHERE name = :name', ['name' => $name]);
                    if (!empty($scope)) {
                        $this->addSql('UPDATE fusio_scope_routes SET scope_id = :scope_id WHERE route_id = :route_id', ['scope_id' => $scope['id'], 'route_id' => $row['id']]);
                    }
                }
            }
        }
    }
}
