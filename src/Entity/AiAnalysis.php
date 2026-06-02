<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AiAnalysis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'analysis')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', unique: true)]
    private RssItem $rssItem;

    #[ORM\Column(type: Types::TEXT)]
    private string $summary;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $translatedTitle = null;

    #[ORM\Column]
    private int $relevanceScore;

    #[ORM\Column]
    private int $businessScore;

    #[ORM\Column]
    private int $learningScore;

    #[ORM\Column]
    private int $contentScore;

    #[ORM\Column]
    private int $finalScore;

    #[ORM\Column(nullable: true)]
    private ?int $userRelevanceScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $userBusinessScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $userLearningScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $userContentScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $userFinalScore = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $userScoresUpdatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reasoning = null;

    #[ORM\Column(length: 120)]
    private string $modelUsed;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        RssItem $rssItem,
        string $summary,
        int $relevanceScore,
        int $businessScore,
        int $learningScore,
        int $contentScore,
        int $finalScore,
        ?string $reasoning,
        string $modelUsed,
    ) {
        $this->rssItem = $rssItem;
        $this->summary = $summary;
        $this->relevanceScore = $relevanceScore;
        $this->businessScore = $businessScore;
        $this->learningScore = $learningScore;
        $this->contentScore = $contentScore;
        $this->finalScore = $finalScore;
        $this->reasoning = $reasoning;
        $this->modelUsed = $modelUsed;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRssItem(): RssItem
    {
        return $this->rssItem;
    }

    public function setRssItem(RssItem $rssItem): self
    {
        $this->rssItem = $rssItem;

        if ($rssItem->getAnalysis() !== $this) {
            $rssItem->setAnalysis($this);
        }

        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getRelevanceScore(): int
    {
        return $this->relevanceScore;
    }

    public function setRelevanceScore(int $relevanceScore): self
    {
        $this->relevanceScore = $relevanceScore;

        return $this;
    }

    public function getBusinessScore(): int
    {
        return $this->businessScore;
    }

    public function setBusinessScore(int $businessScore): self
    {
        $this->businessScore = $businessScore;

        return $this;
    }

    public function getLearningScore(): int
    {
        return $this->learningScore;
    }

    public function setLearningScore(int $learningScore): self
    {
        $this->learningScore = $learningScore;

        return $this;
    }

    public function getContentScore(): int
    {
        return $this->contentScore;
    }

    public function setContentScore(int $contentScore): self
    {
        $this->contentScore = $contentScore;

        return $this;
    }

    public function getFinalScore(): int
    {
        return $this->finalScore;
    }

    public function setFinalScore(int $finalScore): self
    {
        $this->finalScore = $finalScore;

        return $this;
    }

    public function getReasoning(): ?string
    {
        return $this->reasoning;
    }

    public function setReasoning(?string $reasoning): self
    {
        $this->reasoning = $reasoning;

        return $this;
    }

    public function getModelUsed(): string
    {
        return $this->modelUsed;
    }

    public function setModelUsed(string $modelUsed): self
    {
        $this->modelUsed = $modelUsed;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTranslatedTitle(): ?string
    {
        return $this->translatedTitle;
    }

    public function setTranslatedTitle(?string $translatedTitle): self
    {
        $this->translatedTitle = $translatedTitle;

        return $this;
    }

    public function getUserRelevanceScore(): ?int
    {
        return $this->userRelevanceScore;
    }

    public function getUserBusinessScore(): ?int
    {
        return $this->userBusinessScore;
    }

    public function getUserLearningScore(): ?int
    {
        return $this->userLearningScore;
    }

    public function getUserContentScore(): ?int
    {
        return $this->userContentScore;
    }

    public function getUserFinalScore(): ?int
    {
        return $this->userFinalScore;
    }

    public function getUserScoresUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->userScoresUpdatedAt;
    }

    public function applyUserScores(
        int $relevance,
        int $business,
        int $learning,
        int $content,
        int $final,
    ): self {
        $this->userRelevanceScore = max(0, min(100, $relevance));
        $this->userBusinessScore = max(0, min(100, $business));
        $this->userLearningScore = max(0, min(100, $learning));
        $this->userContentScore = max(0, min(100, $content));
        $this->userFinalScore = max(0, min(100, $final));
        $this->userScoresUpdatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function resetUserScores(): self
    {
        $this->userRelevanceScore = null;
        $this->userBusinessScore = null;
        $this->userLearningScore = null;
        $this->userContentScore = null;
        $this->userFinalScore = null;
        $this->userScoresUpdatedAt = null;

        return $this;
    }

    public function hasUserScores(): bool
    {
        return $this->userScoresUpdatedAt !== null;
    }

    public function getEffectiveFinalScore(): int
    {
        return $this->userFinalScore ?? $this->finalScore;
    }
}
