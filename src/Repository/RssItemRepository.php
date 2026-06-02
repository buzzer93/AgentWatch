<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RssItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RssItem>
 */
final class RssItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RssItem::class);
    }

    /** @return list<RssItem> */
    public function findLatestUnprocessed(int $limit = 25): array
    {
        return $this->createQueryBuilder('item')
            ->andWhere('item.isProcessed = :isProcessed')
            ->setParameter('isProcessed', false)
            ->orderBy('item.importedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<RssItem> */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('item')
            ->innerJoin('item.source', 'source')
            ->addSelect('source')
            ->leftJoin('item.analysis', 'analysis')
            ->addSelect('analysis')
            ->orderBy('item.importedAt', 'DESC')
            ->addOrderBy('item.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<RssItem> */
    public function findRecentAnalyzed(int $limit = 10): array
    {
        return $this->createQueryBuilder('item')
            ->innerJoin('item.source', 'source')
            ->addSelect('source')
            ->leftJoin('item.analysis', 'analysis')
            ->addSelect('analysis')
            ->andWhere('analysis.id IS NOT NULL')
            ->addOrderBy('CASE WHEN item.publishedAt IS NULL THEN item.importedAt ELSE item.publishedAt END', 'DESC')
            ->addOrderBy('item.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<RssItem> */
    public function findPaged(int $offset, int $limit): array
    {
        return $this->createQueryBuilder('item')
            ->innerJoin('item.source', 'source')
            ->addSelect('source')
            ->leftJoin('item.analysis', 'analysis')
            ->addSelect('analysis')
            ->addOrderBy('CASE WHEN item.publishedAt IS NULL THEN item.importedAt ELSE item.publishedAt END', 'DESC')
            ->addOrderBy('item.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('item')
            ->select('COUNT(item.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByUrl(string $url): ?RssItem
    {
        return $this->findOneBy(['url' => $url]);
    }

    public function findOneByHash(string $hash): ?RssItem
    {
        return $this->findOneBy(['hash' => $hash]);
    }
}
