<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Prompt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prompt>
 */
final class PromptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prompt::class);
    }

    /** @return list<Prompt> */
    public function findAllOrderedByKey(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.promptKey', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByKey(string $promptKey): ?Prompt
    {
        return $this->findOneBy(['promptKey' => $promptKey]);
    }
}
