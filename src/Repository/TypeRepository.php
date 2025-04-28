<?php

namespace App\Repository;

use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NetworksType>
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    public function findAllStructureType(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id','t.name')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
