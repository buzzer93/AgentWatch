<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type + scrape_config columns on rss_source (prepare HTML scraping fallback for Phase 13)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE rss_source ADD type VARCHAR(20) NOT NULL DEFAULT 'RSS'");
        $this->addSql('ALTER TABLE rss_source ALTER COLUMN type DROP DEFAULT');
        $this->addSql('ALTER TABLE rss_source ADD scrape_config JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rss_source DROP scrape_config');
        $this->addSql('ALTER TABLE rss_source DROP type');
    }
}
