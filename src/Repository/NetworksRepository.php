<?php

namespace App\Repository;

use App\Entity\Networks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Networks>
 */
class NetworksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Networks::class);
    }

    /**
     * @Récupère tous les noms des réseaux
     */
    public function findAllNetworkNames(): array
    {
        return $this->createQueryBuilder('n')
            ->select('n.name')
            ->orderBy('n.name', 'ASC')
            ->getQuery()
            ->getSingleColumnResult(); // Retourne uniquement une colonne (name)
    }

    //    /**
    //     * @return Networks[] Returns an array of Networks objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Networks
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
