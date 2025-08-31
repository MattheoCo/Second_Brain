<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831065749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__course AS SELECT id, name, code, ects, description FROM course');
        $this->addSql('DROP TABLE course');
        $this->addSql('CREATE TABLE course (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, name VARCHAR(180) NOT NULL, code VARCHAR(50) DEFAULT NULL, ects DOUBLE PRECISION DEFAULT NULL, description CLOB DEFAULT NULL, CONSTRAINT FK_169E6FB9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO course (id, name, code, ects, description) SELECT id, name, code, ects, description FROM __temp__course');
        $this->addSql('DROP TABLE __temp__course');
        $this->addSql('CREATE INDEX IDX_169E6FB9A76ED395 ON course (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_data AS SELECT id, user_id, namespace, data FROM user_data');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('CREATE TABLE user_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, namespace VARCHAR(32) NOT NULL, data CLOB NOT NULL, CONSTRAINT FK_D772BFAAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_data (id, user_id, namespace, data) SELECT id, user_id, namespace, data FROM __temp__user_data');
        $this->addSql('DROP TABLE __temp__user_data');
        $this->addSql('CREATE INDEX IDX_D772BFAAA76ED395 ON user_data (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__course AS SELECT id, name, code, ects, description FROM course');
        $this->addSql('DROP TABLE course');
        $this->addSql('CREATE TABLE course (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(180) NOT NULL, code VARCHAR(50) DEFAULT NULL, ects DOUBLE PRECISION DEFAULT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO course (id, name, code, ects, description) SELECT id, name, code, ects, description FROM __temp__course');
        $this->addSql('DROP TABLE __temp__course');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_data AS SELECT id, user_id, namespace, data FROM user_data');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('CREATE TABLE user_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, namespace VARCHAR(32) NOT NULL, data CLOB NOT NULL, CONSTRAINT FK_D772BFAAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_data (id, user_id, namespace, data) SELECT id, user_id, namespace, data FROM __temp__user_data');
        $this->addSql('DROP TABLE __temp__user_data');
        $this->addSql('CREATE INDEX IDX_D772BFAAA76ED395 ON user_data (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_ns ON user_data (user_id, namespace)');
    }
}
