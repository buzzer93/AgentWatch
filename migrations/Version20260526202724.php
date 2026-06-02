<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526202724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_analysis ADD translated_title VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_analysis ADD user_relevance_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_analysis ADD user_business_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_analysis ADD user_learning_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_analysis ADD user_content_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_analysis ADD user_final_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_analysis ADD user_scores_updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_analysis DROP translated_title');
        $this->addSql('ALTER TABLE ai_analysis DROP user_relevance_score');
        $this->addSql('ALTER TABLE ai_analysis DROP user_business_score');
        $this->addSql('ALTER TABLE ai_analysis DROP user_learning_score');
        $this->addSql('ALTER TABLE ai_analysis DROP user_content_score');
        $this->addSql('ALTER TABLE ai_analysis DROP user_final_score');
        $this->addSql('ALTER TABLE ai_analysis DROP user_scores_updated_at');
    }
}
