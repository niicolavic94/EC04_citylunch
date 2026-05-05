<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260505131453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE livreur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_EB7A4E6DE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sac (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, livreur_id INT NOT NULL, UNIQUE INDEX UNIQ_1AB651FF8646701 (livreur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sac_produit (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, sac_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_6F43850228B5C4FE (sac_id), INDEX IDX_6F438502F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sac ADD CONSTRAINT FK_1AB651FF8646701 FOREIGN KEY (livreur_id) REFERENCES livreur (id)');
        $this->addSql('ALTER TABLE sac_produit ADD CONSTRAINT FK_6F43850228B5C4FE FOREIGN KEY (sac_id) REFERENCES sac (id)');
        $this->addSql('ALTER TABLE sac_produit ADD CONSTRAINT FK_6F438502F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sac DROP FOREIGN KEY FK_1AB651FF8646701');
        $this->addSql('ALTER TABLE sac_produit DROP FOREIGN KEY FK_6F43850228B5C4FE');
        $this->addSql('ALTER TABLE sac_produit DROP FOREIGN KEY FK_6F438502F347EFB');
        $this->addSql('DROP TABLE livreur');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE sac');
        $this->addSql('DROP TABLE sac_produit');
    }
}
