<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop ai_analysis.main_category';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_analysis DROP main_category');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_analysis ADD main_category VARCHAR(255) NOT NULL DEFAULT \'OTHER\'');
        $this->addSql('ALTER TABLE ai_analysis ALTER main_category DROP DEFAULT');
    }
}

