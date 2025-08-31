<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250830215845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_data AS SELECT id, user_id FROM user_data');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('CREATE TABLE user_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, namespace VARCHAR(32) NOT NULL, data CLOB NOT NULL, CONSTRAINT FK_D772BFAAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_data (id, user_id) SELECT id, user_id FROM __temp__user_data');
        $this->addSql('DROP TABLE __temp__user_data');
        $this->addSql('CREATE INDEX IDX_D772BFAAA76ED395 ON user_data (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_data AS SELECT id, user_id FROM user_data');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('CREATE TABLE user_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_D772BFAAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_data (id, user_id) SELECT id, user_id FROM __temp__user_data');
        $this->addSql('DROP TABLE __temp__user_data');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAAA76ED395 ON user_data (user_id)');
    }
}
