<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831070452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE user_data_share (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, target_id INTEGER NOT NULL, namespace VARCHAR(32) NOT NULL, status VARCHAR(16) NOT NULL, can_write BOOLEAN NOT NULL, CONSTRAINT FK_6DE97297E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6DE9729158E0B66 FOREIGN KEY (target_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6DE97297E3C61F9 ON user_data_share (owner_id)');
        $this->addSql('CREATE INDEX IDX_6DE9729158E0B66 ON user_data_share (target_id)');
    $this->addSql('CREATE UNIQUE INDEX uniq_share_owner_target_ns ON user_data_share (owner_id, target_id, namespace)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_data_share');
    }
}
