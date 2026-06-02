<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\FeedbackType;
use App\Repository\UserFeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserFeedbackRepository::class)]
#[ORM\Index(columns: ['created_at'], name: 'idx_user_feedback_created_at')]
class UserFeedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private RssItem $rssItem;

    #[ORM\Column(enumType: FeedbackType::class)]
    private FeedbackType $feedbackType;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(RssItem $rssItem, FeedbackType $feedbackType)
    {
        $this->rssItem = $rssItem;
        $this->feedbackType = $feedbackType;
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

        return $this;
    }

    public function getFeedbackType(): FeedbackType
    {
        return $this->feedbackType;
    }

    public function setFeedbackType(FeedbackType $feedbackType): self
    {
        $this->feedbackType = $feedbackType;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}