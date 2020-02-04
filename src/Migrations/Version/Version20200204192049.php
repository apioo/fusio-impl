<?php declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Migrations\MigrationUtil;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200204192049 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // migrate period column
        $planTable = $schema->getTable('fusio_plan');
        if ($planTable->hasColumn('period')) {
            $planTable->dropColumn('period');

            $this->addSql('UPDATE fusio_plan SET period_type = period');
        }

        $planContractTable = $schema->getTable('fusio_plan_contract');
        if ($planContractTable->hasColumn('period')) {
            $planContractTable->dropColumn('period');

            $this->addSql('UPDATE fusio_plan_contract SET period_type = period');
        }

        // sync
        MigrationUtil::sync($this->connection, function($sql, $params){
            $this->addSql($sql, $params);
        });
    }

    public function down(Schema $schema) : void
    {
    }
}
