<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526184243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rss_item_tag (rss_item_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY (rss_item_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_E54F4E121A9CF71F ON rss_item_tag (rss_item_id)');
        $this->addSql('CREATE INDEX IDX_E54F4E12BAD26311 ON rss_item_tag (tag_id)');
        $this->addSql('ALTER TABLE rss_item_tag ADD CONSTRAINT FK_E54F4E121A9CF71F FOREIGN KEY (rss_item_id) REFERENCES rss_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rss_item_tag ADD CONSTRAINT FK_E54F4E12BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_analysis DROP tags');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rss_item_tag DROP CONSTRAINT FK_E54F4E121A9CF71F');
        $this->addSql('ALTER TABLE rss_item_tag DROP CONSTRAINT FK_E54F4E12BAD26311');
        $this->addSql('DROP TABLE rss_item_tag');
        $this->addSql('ALTER TABLE ai_analysis ADD tags JSON NOT NULL');
    }
}
