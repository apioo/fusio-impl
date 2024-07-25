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

        $count = 0;
        foreach ($operations as $httpPath) {
            $count += $this->connection->update('fusio_operation', ['status' => Operation::STATUS_DELETED], ['http_path' => $httpPath]);
        }

        // add scopes
        $scopeId = $this->connection->fetchOne('SELECT id FROM fusio_scope WHERE name = :name', ['name' => 'backend.marketplace']);
        if (empty($scopeId)) {
            return;
        }

        if ($count > 0) {
            $operationIds = $this->connection->fetchFirstColumn('SELECT id FROM fusio_operation WHERE status = :status AND http_path LIKE :path', [
                'status' => Operation::STATUS_ACTIVE,
                'path' => '/backend/marketplace/%',
            ]);

            foreach ($operationIds as $operationId) {
                $id = $this->connection->fetchOne('SELECT id FROM fusio_scope_operation WHERE scope_id = :scope_id AND operation_id LIKE :operation_id', [
                    'scope_id' => $scopeId,
                    'operation_id' => $operationId,
                ]);

                if (empty($id)) {
                    $this->connection->insert('fusio_scope_operation', [
                        'scope_id' => $scopeId,
                        'operation_id' => $operationId,
                        'allow' => 1,
                    ]);
                }
            }
        }
    }
}
