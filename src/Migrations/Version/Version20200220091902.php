<?php declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200220091902 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // add internal events
        $events = [
            'fusio.action.create',
            'fusio.action.delete',
            'fusio.action.update',
            'fusio.app.create',
            'fusio.app.delete',
            'fusio.app.update',
            'fusio.connection.create',
            'fusio.connection.delete',
            'fusio.connection.update',
            'fusio.cronjob.create',
            'fusio.cronjob.delete',
            'fusio.cronjob.update',
            'fusio.event.create',
            'fusio.event.delete',
            'fusio.event.update',
            'fusio.event.subscription.create',
            'fusio.event.subscription.delete',
            'fusio.event.subscription.update',
            'fusio.plan.create',
            'fusio.plan.delete',
            'fusio.plan.update',
            'fusio.rate.create',
            'fusio.rate.delete',
            'fusio.rate.update',
            'fusio.routes.create',
            'fusio.routes.delete',
            'fusio.routes.update',
            'fusio.schema.create',
            'fusio.schema.delete',
            'fusio.schema.update',
            'fusio.scope.create',
            'fusio.scope.delete',
            'fusio.scope.update',
            'fusio.user.create',
            'fusio.user.delete',
            'fusio.user.update',
        ];

        foreach ($events as $eventName) {
            $this->addSql('INSERT INTO fusio_event (status, name, description) VALUES (:status, :name, :description)', [
                'status' => Table\Event::STATUS_INTERNAL,
                'name' => $eventName,
                'description' => '',
            ]);
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
