<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RssItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RssItemRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_rss_item_url', columns: ['url'])]
#[ORM\Index(columns: ['hash'], name: 'idx_rss_item_hash')]
#[ORM\Index(columns: ['is_processed'], name: 'idx_rss_item_processed')]
#[ORM\Index(columns: ['published_at'], name: 'idx_rss_item_published_at')]
class RssItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rssItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private RssSource $source;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 2048)]
    private string $url;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $author = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $importedAt;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawExcerpt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawContent = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(length: 64)]
    private string $hash;

    #[ORM\Column]
    private bool $isProcessed = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(mappedBy: 'rssItem', targetEntity: AiAnalysis::class, cascade: ['persist', 'remove'])]
    private ?AiAnalysis $analysis = null;

    /** @var Collection<int, UserFeedback> */
    #[ORM\OneToMany(mappedBy: 'rssItem', targetEntity: UserFeedback::class, cascade: ['remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $feedbacks;

    /** @var Collection<int, DailySelectionItem> */
    #[ORM\OneToMany(mappedBy: 'rssItem', targetEntity: DailySelectionItem::class, cascade: ['remove'])]
    private Collection $dailySelectionItems;

    /** @var Collection<int, Tag> */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'rssItems')]
    #[ORM\JoinTable(name: 'rss_item_tag')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $tags;

    public function __construct(RssSource $source, string $title, string $url, string $hash)
    {
        $this->source = $source;
        $this->title = $title;
        $this->url = $url;
        $this->hash = $hash;
        $this->importedAt = new \DateTimeImmutable();
        $this->createdAt = $this->importedAt;
        $this->updatedAt = $this->importedAt;
        $this->feedbacks = new ArrayCollection();
        $this->dailySelectionItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): RssSource
    {
        return $this->source;
    }

    public function setSource(RssSource $source): self
    {
        $this->source = $source;
        $this->touch();

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->touch();

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->touch();

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;
        $this->touch();

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        $this->touch();

        return $this;
    }

    public function getImportedAt(): \DateTimeImmutable
    {
        return $this->importedAt;
    }

    public function setImportedAt(\DateTimeImmutable $importedAt): self
    {
        $this->importedAt = $importedAt;
        $this->touch();

        return $this;
    }

    public function getRawExcerpt(): ?string
    {
        return $this->rawExcerpt;
    }

    public function setRawExcerpt(?string $rawExcerpt): self
    {
        $this->rawExcerpt = $rawExcerpt;
        $this->touch();

        return $this;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    public function setRawContent(?string $rawContent): self
    {
        $this->rawContent = $rawContent;
        $this->touch();

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        $this->touch();

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        $this->touch();

        return $this;
    }

    public function isProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): self
    {
        $this->isProcessed = $isProcessed;
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAnalysis(): ?AiAnalysis
    {
        return $this->analysis;
    }

    public function setAnalysis(?AiAnalysis $analysis): self
    {
        if ($analysis !== null && $analysis->getRssItem() !== $this) {
            $analysis->setRssItem($this);
        }

        $this->analysis = $analysis;
        $this->touch();

        return $this;
    }

    /** @return Collection<int, UserFeedback> */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(UserFeedback $feedback): self
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setRssItem($this);
        }

        return $this;
    }

    public function removeFeedback(UserFeedback $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback) && $feedback->getRssItem() === $this) {
            $feedback->setRssItem($this);
        }

        return $this;
    }

    /** @return Collection<int, DailySelectionItem> */
    public function getDailySelectionItems(): Collection
    {
        return $this->dailySelectionItems;
    }

    public function addDailySelectionItem(DailySelectionItem $dailySelectionItem): self
    {
        if (!$this->dailySelectionItems->contains($dailySelectionItem)) {
            $this->dailySelectionItems->add($dailySelectionItem);
            $dailySelectionItem->setRssItem($this);
        }

        return $this;
    }

    public function removeDailySelectionItem(DailySelectionItem $dailySelectionItem): self
    {
        if ($this->dailySelectionItems->removeElement($dailySelectionItem) && $dailySelectionItem->getRssItem() === $this) {
            $dailySelectionItem->setRssItem($this);
        }

        return $this;
    }

    /** @return Collection<int, Tag> */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $this->touch();
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $this->touch();
        }

        return $this;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}