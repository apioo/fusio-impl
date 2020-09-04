<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Migrations\MigrationUtil;
use Fusio\Impl\Service\Routes\Config;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190707074312 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $routesMethodTable = $schema->getTable('fusio_routes_method');
        $routesMethodTable->addColumn('operation_id', 'string', ['notnull' => false, 'length' => 255]);

        // sync
        MigrationUtil::sync($this->connection, function($sql, $params){
            $this->addSql($sql, $params);
        });
    }

    public function postUp(Schema $schema): void
    {
        $sql = 'SELECT method.id,
                       method.method,
                       routes.path
                  FROM fusio_routes_method method
            INNER JOIN fusio_routes routes
                    ON routes.id = method.route_id
                 WHERE method.operation_id IS NULL';

        $result = $this->connection->fetchAll($sql);
        foreach ($result as $row) {
            $this->addSql('UPDATE fusio_routes_method SET operation_id = :operation_id WHERE id = :id', [
                'operation_id' => Config::buildOperationId($row['path'], $row['method']),
                'id' => $row['id']
            ]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
