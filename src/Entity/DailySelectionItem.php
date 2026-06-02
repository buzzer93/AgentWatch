<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_daily_selection_rank', columns: ['daily_selection_id', 'rank'])]
#[ORM\UniqueConstraint(name: 'uniq_daily_selection_item', columns: ['daily_selection_id', 'rss_item_id'])]
class DailySelectionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DailySelection $dailySelection;

    #[ORM\ManyToOne(inversedBy: 'dailySelectionItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private RssItem $rssItem;

    #[ORM\Column]
    private int $rank;

    #[ORM\Column(type: Types::TEXT)]
    private string $selectionReason;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(DailySelection $dailySelection, RssItem $rssItem, int $rank, string $selectionReason)
    {
        $this->dailySelection = $dailySelection;
        $this->rssItem = $rssItem;
        $this->rank = $rank;
        $this->selectionReason = $selectionReason;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDailySelection(): DailySelection
    {
        return $this->dailySelection;
    }

    public function setDailySelection(DailySelection $dailySelection): self
    {
        $this->dailySelection = $dailySelection;

        return $this;
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

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getSelectionReason(): string
    {
        return $this->selectionReason;
    }

    public function setSelectionReason(string $selectionReason): self
    {
        $this->selectionReason = $selectionReason;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}