<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220408221409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE bet_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE "prediction_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "prediction" (id INT NOT NULL, user_id INT DEFAULT NULL, game_id VARCHAR(255) DEFAULT NULL, team VARCHAR(255) NOT NULL, realised BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_36396FC8A76ED395 ON "prediction" (user_id)');
        $this->addSql('CREATE INDEX IDX_36396FC8E48FD905 ON "prediction" (game_id)');
        $this->addSql('ALTER TABLE "prediction" ADD CONSTRAINT FK_36396FC8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "prediction" ADD CONSTRAINT FK_36396FC8E48FD905 FOREIGN KEY (game_id) REFERENCES "game" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE bet');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE "prediction_id_seq" CASCADE');
        $this->addSql('CREATE SEQUENCE bet_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bet (id INT NOT NULL, user_id INT DEFAULT NULL, game_id VARCHAR(255) DEFAULT NULL, prediction VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_fbf0ec9be48fd905 ON bet (game_id)');
        $this->addSql('CREATE INDEX idx_fbf0ec9ba76ed395 ON bet (user_id)');
        $this->addSql('ALTER TABLE bet ADD CONSTRAINT fk_fbf0ec9ba76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bet ADD CONSTRAINT fk_fbf0ec9be48fd905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE "prediction"');
    }
}
