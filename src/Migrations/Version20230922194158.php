<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230922194158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $eventResponseTable = $schema->getTable('fusio_event_response');
        if (!$eventResponseTable->hasColumn('body')) {
            $eventResponseTable->dropColumn('trigger_id');
            $eventResponseTable->dropColumn('error');
            $eventResponseTable->addColumn('body', 'text', ['notnull' => false]);
        }
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
