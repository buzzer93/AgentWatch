<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\SourceType;
use App\Repository\RssSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RssSourceRepository::class)]
#[ORM\Index(columns: ['is_active'], name: 'idx_rss_source_active')]
#[UniqueEntity(fields: ['feedUrl'], message: 'Cette URL de flux est deja enregistree.')]
class RssSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(length: 2048, unique: true)]
    #[Assert\NotBlank(message: 'L URL du flux est obligatoire.')]
    #[Assert\Url(message: 'L URL du flux n est pas valide.')]
    #[Assert\Length(max: 2048)]
    private string $feedUrl;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Assert\Url(message: 'L URL du site n est pas valide.')]
    #[Assert\Length(max: 2048)]
    private ?string $websiteUrl = null;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'La priorite doit etre entre {{ min }} et {{ max }}.')]
    private int $priority = 0;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(length: 20, enumType: SourceType::class)]
    private SourceType $type = SourceType::RSS;

    /** @var array<string, string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $scrapeConfig = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, \App\Entity\RssItem> */
    #[ORM\OneToMany(mappedBy: 'source', targetEntity: \App\Entity\RssItem::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $rssItems;

    /** @var Collection<int, Tag> */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'rssSources')]
    #[ORM\JoinTable(name: 'rss_source_tag')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $tags;

    public function __construct(string $name, string $feedUrl)
    {
        $this->name = $name;
        $this->feedUrl = $feedUrl;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->rssItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->touch();

        return $this;
    }

    public function getFeedUrl(): string
    {
        return $this->feedUrl;
    }

    public function setFeedUrl(string $feedUrl): self
    {
        $this->feedUrl = $feedUrl;
        $this->touch();

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): self
    {
        $this->websiteUrl = $websiteUrl;
        $this->touch();

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        $this->touch();

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->touch();

        return $this;
    }

    public function getType(): SourceType
    {
        return $this->type;
    }

    public function setType(SourceType $type): self
    {
        $this->type = $type;
        $this->touch();

        return $this;
    }

    /** @return array<string, string>|null */
    public function getScrapeConfig(): ?array
    {
        return $this->scrapeConfig;
    }

    /** @param array<string, string>|null $scrapeConfig */
    public function setScrapeConfig(?array $scrapeConfig): self
    {
        $this->scrapeConfig = $scrapeConfig;
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

    /** @return Collection<int, \App\Entity\RssItem> */
    public function getRssItems(): Collection
    {
        return $this->rssItems;
    }

    public function addRssItem(\App\Entity\RssItem $rssItem): self
    {
        if (!$this->rssItems->contains($rssItem)) {
            $this->rssItems->add($rssItem);
            $rssItem->setSource($this);
        }

        return $this;
    }

    public function removeRssItem(\App\Entity\RssItem $rssItem): self
    {
        $this->rssItems->removeElement($rssItem);

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