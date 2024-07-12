<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;
use Fusio\Impl\Table\Operation;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240712210005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function postUp(Schema $schema): void
    {
        DataSyncronizer::sync($this->connection);

        // deactivate legacy operations
        $operations = [
            '/backend/marketplace',
            '/backend/marketplace/:app_name',
        ];

        foreach ($operations as $httpPath) {
            $this->connection->update('fusio_operation', ['status' => Operation::STATUS_DELETED], ['http_path' => $httpPath]);
        }
    }
}
