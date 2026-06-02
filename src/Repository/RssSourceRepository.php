<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RssSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RssSource>
 */
final class RssSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RssSource::class);
    }

    /** @return list<RssSource> */
    public function findActiveOrderedByPriority(): array
    {
        return $this->createQueryBuilder('source')
            ->andWhere('source.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('source.priority', 'DESC')
            ->addOrderBy('source.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<RssSource> */
    public function findAllOrderedByPriority(): array
    {
        return $this->createQueryBuilder('source')
            ->orderBy('source.isActive', 'DESC')
            ->addOrderBy('source.priority', 'DESC')
            ->addOrderBy('source.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}