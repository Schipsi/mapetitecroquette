<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220508185931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "league" (id VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, short_name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO "league" VALUES (\'100695891328981122\', \'EUROPEAN MASTER SPRING 2022\', \'EUM\', \'https://am-a.akamaihd.net/image?resize=60:&f=http%3A%2F%2Fstatic.lolesports.com%2Fleagues%2FEM_Bug_Outline1.png\', false)');
        $this->addSql('INSERT INTO "league" VALUES (\'98767991325878492\', \'MSI 2022\', \'MSI\', \'https://am-a.akamaihd.net/image?resize=60:&f=http%3A%2F%2Fstatic.lolesports.com%2Fleagues%2F1592594634248_MSIDarkBG.png\', true)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "league"');
    }
}
