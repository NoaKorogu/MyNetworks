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

    public function findPathById(int $id): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.color, ST_AsGeoJSON(p.path) AS path_geojson')
            ->where('p.deleted_at IS NULL AND p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
    public function findLastPath(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.color, ST_AsGeoJSON(p.path) AS path_geojson')
            ->where('p.deleted_at IS NULL')
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults(1)
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

    // public function updatePath(int $id, ?string $name, ?string $color): void
    // {
    //     // Build the SQL query dynamically based on provided parameters
    //     $fieldsToUpdate = [];
    //     $parameters = ['id' => $id];

    //     if ($name !== null) {
    //         $fieldsToUpdate[] = 'name = :name';
    //         $parameters['name'] = $name;
    //     }

    //     if ($color !== null) {
    //         $fieldsToUpdate[] = 'color = :color';
    //         $parameters['color'] = $color;
    //     }

    //     if (empty($fieldsToUpdate)) {
    //         throw new \InvalidArgumentException('No fields to update.');
    //     }

    //     $sql = sprintf(
    //         'UPDATE path SET %s, updated_at = NOW() WHERE id = :id',
    //         implode(', ', $fieldsToUpdate)
    //     );

    //     $this->_em->getConnection()->executeStatement($sql, $parameters);
    // }

    public function updatePath(int $id, ?string $name, ?string $color): void // Doit créer un log si un chemin est modifié
    {
        $path = $this->find($id);
        if (!$path) {
            throw new \InvalidArgumentException('Path not found.');
        }

        // Capture old data
        $oldData = [
            'name' => $path->getName(),
            'color' => $path->getColor(),
        ];

        // Update the entity
        if ($name !== null) {
            $path->setName($name);
        }

        if ($color !== null) {
            $path->setColor($color);
        }

        // Capture new data
        $newData = [
            'name' => $path->getName(),
            'color' => $path->getColor(),
        ];

        // Persist the changes
        $this->_em->persist($path);
        $this->_em->flush();

        // Log the update
        $this->databaseService->logAction('path', $id, $oldData, $newData);
    }

}
