<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903225555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_file ADD imported_by_id INT NOT NULL, ADD imported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD is_imported TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE import_file ADD CONSTRAINT FK_61B3D89074953CEA FOREIGN KEY (imported_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_61B3D89074953CEA ON import_file (imported_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_file DROP FOREIGN KEY FK_61B3D89074953CEA');
        $this->addSql('DROP INDEX IDX_61B3D89074953CEA ON import_file');
        $this->addSql('ALTER TABLE import_file DROP imported_by_id, DROP imported_at, DROP is_imported');
    }
}
