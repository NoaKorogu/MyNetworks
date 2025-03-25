<?php

namespace App\Repository;

use App\Entity\Path;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Path>
 */
class PathRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Path::class);
    }

    // public function findAllPaths(): array
    // {
    //     return $this->createQueryBuilder('p')
    //         ->select('p.id, p.name, p.color, ST_AsGeoJSON(p.path) AS path_geojson')
    //         ->getQuery()
    //         ->getResult();
    // }
}

