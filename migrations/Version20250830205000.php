<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250830205000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on user_data(user_id, namespace) and add namespace/data columns if missing (SQLite-safe).';
    }

    public function up(Schema $schema): void
    {
        // Ensure columns and the composite unique index exist
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform === 'sqlite') {
            // Check existing columns
            $colsInfo = $this->connection->fetchAllAssociative("PRAGMA table_info('user_data')");
            $colNames = array_map(fn($r) => $r['name'] ?? $r['Name'] ?? null, $colsInfo);
            $hasNamespace = in_array('namespace', $colNames, true);
            $hasData = in_array('data', $colNames, true);
            if (!$hasNamespace) {
                $this->addSql("ALTER TABLE user_data ADD COLUMN namespace VARCHAR(32) NOT NULL DEFAULT 'default'");
            }
            if (!$hasData) {
                $this->addSql("ALTER TABLE user_data ADD COLUMN data CLOB NOT NULL DEFAULT '{}' ");
            }
            // Normalize namespace
            $this->addSql("UPDATE user_data SET namespace = lower(namespace)");

            // Refresh index list and ensure composite unique index exists
            $idxList = $this->connection->fetchAllAssociative("SELECT name, sql FROM sqlite_master WHERE type='index' AND tbl_name='user_data'");
            $hasComposite = false;
            foreach ($idxList as $idx) {
                if (strtolower($idx['name']) === 'uniq_user_ns') { $hasComposite = true; break; }
            }
            if (!$hasComposite) {
                $this->addSql("CREATE UNIQUE INDEX uniq_user_ns ON user_data (user_id, namespace)");
            }
        } else {
            // Other platforms
            $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_user_ns ON user_data (user_id, namespace)");
        }
    }

    public function down(Schema $schema): void
    {
        // Revert only the index to keep data
        $this->addSql("DROP INDEX IF EXISTS uniq_user_ns");
    }
}
