<?php declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191022185247 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $count = $this->connection->fetchColumn('SELECT COUNT(*) AS cnt FROM fusio_scope WHERE name = :name', ['name' => 'backend.account']);
        $this->skipIf($count > 0, 'Scopes already available');

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
    }

    public function down(Schema $schema) : void
    {
    }
}
