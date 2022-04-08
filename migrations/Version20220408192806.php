<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220408192806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "game" (id VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name_team1 VARCHAR(255) NOT NULL, name_team2 VARCHAR(255) NOT NULL, code_team1 VARCHAR(255) NOT NULL, code_team2 VARCHAR(255) NOT NULL, image_team1 VARCHAR(255) NOT NULL, image_team2 VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, outcome_team1 VARCHAR(255) DEFAULT NULL, outcome_team2 VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "game".date IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "game"');
    }
}
