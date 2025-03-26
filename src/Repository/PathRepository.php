<?php

namespace App\Repository;

use App\Entity\Path;
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
    
    public function findAllPaths(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.color, ST_AsGeoJSON(p.path) AS path_geojson')
            ->where('p.deleted_at IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function createPath(string $name, string $color, array $coordinates, ?int $networkId = null): void
    {
        // Convert coordinates array to WKT format
        $wktCoordinates = implode(
            ', ',
            array_map(fn($coord) => "{$coord[0]} {$coord[1]}", $coordinates)
        );
        $ewkt = "SRID=4326;LINESTRING($wktCoordinates)";

        // Create and execute the query
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO path (name, color, path, network_id, created_at) VALUES (:name, :color, ST_GeomFromEWKT(:path), :networkId, NOW())',
            [
                'name' => $name,
                'color' => $color,
                'path' => $ewkt,
                'networkId' => $networkId,
            ],
            [
                'name' => \PDO::PARAM_STR,
                'color' => \PDO::PARAM_STR,
                'path' => \PDO::PARAM_STR,
                'networkId' => $networkId !== null ? \PDO::PARAM_INT : \PDO::PARAM_NULL,
            ]
        );
    }

    public function deletePathById(int $id): void
    {
        $this->_em->getConnection()->executeStatement(
            'UPDATE path SET deleted_at = NOW() WHERE id = :id',
            ['id' => $id],
            ['id' => \PDO::PARAM_INT]
        );
    }
}
