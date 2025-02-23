<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Installation\DataSyncronizer;
use Fusio\Model\Backend\DatabaseRowCollection;
use Fusio\Model\Backend\DatabaseTableCollection;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223070312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function postUp(Schema $schema): void
    {
        DataSyncronizer::sync($this->connection);

        // migrate outgoing schemas
        $map = [
            'backend.database.getTables' => DatabaseTableCollection::class,
            'backend.database.getRows' => DatabaseRowCollection::class,
        ];
        foreach ($map as $name => $newSchema) {
            $this->connection->update('fusio_operation', [
                'outgoing' => 'php+class://' . ClassName::serialize($newSchema),
            ], [
                'name' => $name,
            ]);
        }
    }
}
