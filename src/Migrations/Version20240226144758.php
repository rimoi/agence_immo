<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240226144758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission ADD tag_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23CBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('CREATE INDEX IDX_9067F23CBAD26311 ON mission (tag_id)');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783BE6CAE90');
        $this->addSql('DROP INDEX IDX_389B783BE6CAE90 ON tag');
        $this->addSql('ALTER TABLE tag DROP mission_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23CBAD26311');
        $this->addSql('DROP INDEX IDX_9067F23CBAD26311 ON mission');
        $this->addSql('ALTER TABLE mission DROP tag_id');
        $this->addSql('ALTER TABLE tag ADD mission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
        $this->addSql('CREATE INDEX IDX_389B783BE6CAE90 ON tag (mission_id)');
    }
}
