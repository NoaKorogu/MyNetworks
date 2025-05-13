<?php

namespace App\Repository;

use App\Entity\BusStop;
use App\Entity\Electrical;
use App\Entity\Water;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Structure>
 */
class StructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Structure::class);
    }

    public function findStructuresByType(string $type): array
    {
        $conn = $this->_em->getConnection();
    
        $sql = '
            SELECT s.id, s.name, s.discriminator, ST_AsGeoJSON(s.location) AS location_geojson,
                   t.name AS type_name'; // Récupérer le nom du type
    
        if ($type === 'electrical') {
            $sql .= ', e.capacity';
        } elseif ($type === 'water') {
            $sql .= ', w.water_pressure, w.is_open';
        } elseif ($type === 'bus_stop') {
            $sql .= ', b.line_number';
        }
    
        $sql .= '
            FROM structure s
            LEFT JOIN type t ON s.type_id = t.id'; // Jointure avec la table `type`
    
        if ($type === 'electrical') {
            $sql .= ' LEFT JOIN electrical e ON s.id = e.id';
        } elseif ($type === 'water') {
            $sql .= ' LEFT JOIN water w ON s.id = w.id';
        } elseif ($type === 'bus_stop') {
            $sql .= ' LEFT JOIN bus_stop b ON s.id = b.id';
        }
    
        $sql .= '
            WHERE s.discriminator = :type AND s.deleted_at IS NULL';
    
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['type' => $type]);
    
        return $result->fetchAllAssociative();
    }

    public function createStructure(string $name, float $lon, float $lat, int $typeId, int $networkId, int $createdById, string $discriminator): void
    {
        // Convert coordinates to EWKT format
        $ewkt = "SRID=4326;POINT($lon $lat)";
    
        // Create and execute the query
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO structure (name, location, type_id, network_id, created_by_id, discriminator, created_at, updated_at)
             VALUES (:name, ST_GeomFromEWKT(:location), :typeId, :networkId, :createdById, :discriminator, NOW(), NOW())',
            [
                'name' => $name,
                'location' => $ewkt,
                'typeId' => $typeId,
                'networkId' => $networkId,
                'createdById' => $createdById,
                'discriminator' => $discriminator,
            ],
            [
                'name' => \PDO::PARAM_STR,
                'location' => \PDO::PARAM_STR,
                'typeId' => \PDO::PARAM_INT,
                'networkId' => \PDO::PARAM_INT,
                'createdById' => \PDO::PARAM_INT,
                'discriminator' => \PDO::PARAM_STR,
            ]
        );
    }

    public function createElectricalStructure(string $name, float $lon, float $lat, int $typeId, int $networkId, int $createdById, string $capacity): void
    {
        $ewkt = "SRID=4326;POINT($lon $lat)";

        // Insérer dans la table `structure`
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO structure (name, location, type_id, network_id, created_by_id, discriminator, created_at, updated_at)
            VALUES (:name, ST_GeomFromEWKT(:location), :typeId, :networkId, :createdById, :discriminator, NOW(), NOW())',
            [
                'name' => $name,
                'location' => $ewkt,
                'typeId' => $typeId,
                'networkId' => $networkId,
                'createdById' => $createdById,
                'discriminator' => 'electrical',
            ],
            [
                'name' => \PDO::PARAM_STR,
                'location' => \PDO::PARAM_STR,
                'typeId' => \PDO::PARAM_INT,
                'networkId' => \PDO::PARAM_INT,
                'createdById' => \PDO::PARAM_INT,
                'discriminator' => \PDO::PARAM_STR,
            ]
        );

    }

    public function createWaterStructure(string $name, float $lon, float $lat, int $typeId, int $networkId, int $createdById, ?string $waterPressure, bool $isOpen): void
    {
        $ewkt = "SRID=4326;POINT($lon $lat)";

        // Insérer dans la table `structure`
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO structure (name, location, type_id, network_id, created_by_id, discriminator, created_at, updated_at)
            VALUES (:name, ST_GeomFromEWKT(:location), :typeId, :networkId, :createdById, :discriminator, NOW(), NOW())',
            [
                'name' => $name,
                'location' => $ewkt,
                'typeId' => $typeId,
                'networkId' => $networkId,
                'createdById' => $createdById,
                'discriminator' => 'water',
            ],
            [
                'name' => \PDO::PARAM_STR,
                'location' => \PDO::PARAM_STR,
                'typeId' => \PDO::PARAM_INT,
                'networkId' => \PDO::PARAM_INT,
                'createdById' => \PDO::PARAM_INT,
                'discriminator' => \PDO::PARAM_STR,
            ]
        );
    }

    public function createBusStopStructure(string $name, float $lon, float $lat, int $typeId, int $networkId, int $createdById, string $lineNumber): void
    {
        $ewkt = "SRID=4326;POINT($lon $lat)";

        // Insérer dans la table `structure`
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO structure (name, location, type_id, network_id, created_by_id, discriminator, created_at, updated_at)
            VALUES (:name, ST_GeomFromEWKT(:location), :typeId, :networkId, :createdById, :discriminator, NOW(), NOW())',
            [
                'name' => $name,
                'location' => $ewkt,
                'typeId' => $typeId,
                'networkId' => $networkId,
                'createdById' => $createdById,
                'discriminator' => 'bus_stop',
            ],
            [
                'name' => \PDO::PARAM_STR,
                'location' => \PDO::PARAM_STR,
                'typeId' => \PDO::PARAM_INT,
                'networkId' => \PDO::PARAM_INT,
                'createdById' => \PDO::PARAM_INT,
                'discriminator' => \PDO::PARAM_STR,
            ]
        );
    }

    public function createElectrical(int $id, string $capacity): void
    {
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO electrical (id, capacity) VALUES (:id, :capacity)',
            [
                'id' => $id,
                'capacity' => $capacity,
            ],
            [
                'id' => \PDO::PARAM_INT,
                'capacity' => \PDO::PARAM_STR,
            ]
        );
    }

    public function createWater(int $id, ?string $waterPressure, bool $isOpen): void
    {
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO water (id, water_pressure, is_open) VALUES (:id, :waterPressure, :isOpen)',
            [
                'id' => $id,
                'waterPressure' => $waterPressure,
                'isOpen' => $isOpen,
            ],
            [
                'id' => \PDO::PARAM_INT,
                'waterPressure' => \PDO::PARAM_STR,
                'isOpen' => \PDO::PARAM_BOOL,
            ]
        );
    }

    public function createBusStop(int $id, string $lineNumber): void
    {
        $this->_em->getConnection()->executeStatement(
            'INSERT INTO bus_stop (id, line_number) VALUES (:id, :lineNumber)',
            [
                'id' => $id,
                'lineNumber' => $lineNumber,
            ],
            [
                'id' => \PDO::PARAM_INT,
                'lineNumber' => \PDO::PARAM_STR,
            ]
        );
    }

    public function updateStructure(int $id, ?string $name, ?int $typeId, ?array $additionalData = null): void
    {
        $structure = $this->find($id);
        if (!$structure) {
            throw new \InvalidArgumentException('Structure not found.');
        }
    
        // Mise à jour du nom
        if ($name !== null) {
            $structure->setName($name);
        }
    
        // Mise à jour du type
        if ($typeId !== null) {
            $type = $this->_em->getRepository(Type::class)->find($typeId);
            if (!$type) {
                throw new \InvalidArgumentException('Invalid type ID.');
            }
            $structure->setNetworkTypeId($type);
        }
    
        // Mise à jour des données supplémentaires
        if ($additionalData) {
            if ($structure instanceof Electrical && isset($additionalData['capacity'])) {
                $structure->setCapacity($additionalData['capacity']);
            } elseif ($structure instanceof Water) {
                if (isset($additionalData['water_pressure'])) {
                    $structure->setWaterPressure($additionalData['water_pressure']);
                }
                if (isset($additionalData['is_open'])) {
                    $structure->setOpen($additionalData['is_open']);
                }
            } elseif ($structure instanceof BusStop && isset($additionalData['line_number'])) {
                $structure->setLineNumber($additionalData['line_number']);
            }
        }
    
        // Persister les modifications
        $this->_em->persist($structure);
        $this->_em->flush();
    }

    public function deleteStructureById(int $id): void
    {
        $this->_em->getConnection()->executeStatement(
            'UPDATE structure SET deleted_at = NOW() WHERE id = :id',
            ['id' => $id],
            ['id' => \PDO::PARAM_INT]
        );
    }


    //    /**
    //     * @return Structure[] Returns an array of Structure objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Structure
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
