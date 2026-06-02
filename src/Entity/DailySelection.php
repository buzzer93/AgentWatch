<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DailySelectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailySelectionRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_daily_selection_date', columns: ['selection_date'])]
class DailySelection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $selectionDate;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $globalSummary;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, DailySelectionItem> */
    #[ORM\OneToMany(mappedBy: 'dailySelection', targetEntity: DailySelectionItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['rank' => 'ASC'])]
    private Collection $items;

    public function __construct(\DateTimeImmutable $selectionDate, string $title, string $globalSummary)
    {
        $this->selectionDate = $selectionDate;
        $this->title = $title;
        $this->globalSummary = $globalSummary;
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelectionDate(): \DateTimeImmutable
    {
        return $this->selectionDate;
    }

    public function setSelectionDate(\DateTimeImmutable $selectionDate): self
    {
        $this->selectionDate = $selectionDate;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getGlobalSummary(): string
    {
        return $this->globalSummary;
    }

    public function setGlobalSummary(string $globalSummary): self
    {
        $this->globalSummary = $globalSummary;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, DailySelectionItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(DailySelectionItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setDailySelection($this);
        }

        return $this;
    }

    public function removeItem(DailySelectionItem $item): self
    {
        $this->items->removeElement($item);

        return $this;
    }
}