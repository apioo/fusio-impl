<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220212130627 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $eventTable = $schema->getTable('fusio_event');
        if ($eventTable instanceof Table && !$eventTable->hasColumn('event_schema')) {
            $eventTable->addColumn('event_schema', 'string', ['notnull' => false]);
            $this->addSql('UPDATE fusio_event SET event_schema = ' . $this->connection->quoteIdentifier('schema'));
            $eventTable->dropColumn('schema');
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
