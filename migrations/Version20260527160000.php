<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix Martin Fowler RSS feed URL (www subdomain)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE rss_source SET feed_url = 'https://www.martinfowler.com/feed.atom', updated_at = CURRENT_TIMESTAMP WHERE feed_url = 'https://martinfowler.com/feed.atom'",
        );
        $this->addSql(
            "UPDATE rss_source SET website_url = 'https://www.martinfowler.com', updated_at = CURRENT_TIMESTAMP WHERE website_url = 'https://martinfowler.com' AND feed_url = 'https://www.martinfowler.com/feed.atom'",
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "UPDATE rss_source SET feed_url = 'https://martinfowler.com/feed.atom', updated_at = CURRENT_TIMESTAMP WHERE feed_url = 'https://www.martinfowler.com/feed.atom'",
        );
        $this->addSql(
            "UPDATE rss_source SET website_url = 'https://martinfowler.com', updated_at = CURRENT_TIMESTAMP WHERE website_url = 'https://www.martinfowler.com' AND feed_url = 'https://martinfowler.com/feed.atom'",
        );
    }
}

