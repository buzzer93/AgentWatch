<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DailySelection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailySelection>
 */
final class DailySelectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailySelection::class);
    }

    public function findLatest(): ?DailySelection
    {
        return $this->createQueryBuilder('selection')
            ->orderBy('selection.selectionDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneBySelectionDate(\DateTimeImmutable $selectionDate): ?DailySelection
    {
        return $this->findOneBy(['selectionDate' => $selectionDate]);
    }
}