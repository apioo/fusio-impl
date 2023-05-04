<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220605193108 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
    }

    public function down(Schema $schema) : void
    {
    }

    public function postUp(Schema $schema): void
    {
        // deactivate old contract and invoice routes
        $routes = [
            '/backend/plan/contract',
            '/backend/plan/contract/$contract_id<[0-9]+>',
            '/backend/plan/invoice',
            '/backend/plan/invoice/$invoice_id<[0-9]+>',

            '/consumer/plan/contract',
            '/consumer/plan/contract/$contract_id<[0-9]+>',
            '/consumer/plan/invoice',
            '/consumer/plan/invoice/$invoice_id<[0-9]+>',

            '/consumer/transaction/execute/:transaction_id',
            '/consumer/transaction/prepare/:provider',
        ];

        foreach ($routes as $path) {
            $routeId = $this->connection->fetchOne('SELECT id FROM fusio_routes WHERE path = :path', ['path' => $path]);
            if (empty($routeId)) {
                continue;
            }

            $this->connection->update('fusio_routes', ['status' => Table\Route::STATUS_DELETED], ['id' => $routeId]);
        }
    }
}
