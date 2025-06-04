<?php

namespace App\Controller;

use App\Entity\Type;
use App\Entity\Structure;
use App\Entity\Electrical;
use App\Entity\Water;
use App\Entity\User;
use App\Entity\BusStop;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\StructureRepository;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/structures')]
class StructureController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'create_structure', methods: ['POST'])]
    public function createStructure(Request $request, StructureRepository $structureRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Vérification des données
        if (!isset($data['name'], $data['lat'], $data['lon'], $data['typeId'])) {
            return new JsonResponse(['error' => 'Invalid data.'], Response::HTTP_BAD_REQUEST);
        }
    
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }
    
        $type = $this->entityManager->getRepository(Type::class)->find($data['typeId']);
        if (!$type || $type->getNetwork() !== $user->getNetwork()) {
            return new JsonResponse(['error' => 'Invalid type for the user\'s network.'], Response::HTTP_BAD_REQUEST);
        }
    
        // Déterminer le discriminant en fonction du rôle de l'utilisateur
        $discriminator = match ($user->getRoles()[0]) {
            'ROLE_EDF' => 'electrical',
            'ROLE_WATER' => 'water',
            'ROLE_FILIBUS' => 'bus_stop',
            default => 'structure',
        };
    
        try {
            // Appel à la méthode du repository pour insérer la structure dans la table `structure`
            $structureRepository->createStructure(
                name: $data['name'],
                lon: (float)$data['lon'],
                lat: (float)$data['lat'],
                typeId: $data['typeId'],
                networkId: $user->getNetwork()->getId(),
                createdById: $user->getId(),
                discriminator: $discriminator
            );
    
            // Récupérer l'ID de la structure insérée
            $structureId = $this->entityManager->getConnection()->lastInsertId();
    
            // Gérer les données supplémentaires
            if (isset($data['additionalData'])) {
                $additionalData = $data['additionalData'];
    
                if ($discriminator === 'electrical' && isset($additionalData['capacity'])) {
                    // Insérer les données dans la table `electrical`
                    $structureRepository->createElectrical(
                        id: $structureId,
                        capacity: $additionalData['capacity']
                    );
                }
    
                if ($discriminator === 'water' && isset($additionalData['water_pressure'], $additionalData['is_open'])) {
                    // Insérer les données dans la table `water`
                    $structureRepository->createWater(
                        id: $structureId,
                        waterPressure: $additionalData['water_pressure'],
                        isOpen: $additionalData['is_open']
                    );
                }
    
                if ($discriminator === 'bus_stop' && isset($additionalData['line_number'])) {
                    // Insérer les données dans la table `bus_stop`
                    $structureRepository->createBusStop(
                        id: $structureId,
                        lineNumber: $additionalData['line_number']
                    );
                }
            }
    
            return new JsonResponse(['success' => true], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/type/{type}', name: 'get_structures_by_type', methods: ['GET'])]
    public function getStructuresByType(string $type, StructureRepository $structureRepository): JsonResponse
    {
        $structures = $structureRepository->findStructuresByType($type);
    
        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];
    
        foreach ($structures as $structure) {
            $properties = [
                'id' => $structure['id'],
                'name' => $structure['name'],
                'type_name' => $structure['type_name'],
                'type' => $structure['discriminator'],
            ];
    
            // Ajouter la capacité si elle est disponible
            if ($type === 'electrical' && isset($structure['capacity'])) {
                $properties['capacity'] = $structure['capacity'];
            } elseif ($type === 'water') {
                $properties['water_pressure'] = $structure['water_pressure'] ?? null;
                $properties['is_open'] = $structure['is_open'] ?? null;
            } elseif ($type === 'bus_stop') {
                $properties['line_number'] = $structure['line_number'] ?? null;
            }
    
            $geoJson['features'][] = [
                'type' => 'Feature',
                'properties' => $properties,
                'geometry' => json_decode($structure['location_geojson'], true),
            ];
        }
    
        return new JsonResponse($geoJson);
    }

    #[Route('/{id}', name: 'get_structure', methods: ['GET'])]
    public function getStructure(int $id): JsonResponse
    {
        $structure = $this->entityManager->getRepository(Structure::class)->find($id);

        if (!$structure) {
            return new JsonResponse(['error' => 'Structure not found.'], Response::HTTP_NOT_FOUND);
        }

        $geoJson = [
            'type' => 'Feature',
            'properties' => [
                'id' => $structure->getId(),
                'name' => $structure->getName(),
                'type' => $structure->getDiscriminator(), // Assuming discriminator column is accessible
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$structure->getLocation()->getLongitude(), $structure->getLocation()->getLatitude()],
            ],
        ];

        return new JsonResponse($geoJson);
    }

    #[Route('/{id}', name: 'update_structure', methods: ['PUT'])]
    public function updateStructure(Request $request, int $id, StructureRepository $structureRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        try {
            $structureRepository->updateStructure(
                $id,
                $data['name'] ?? null,
                $data['typeId'] ?? null,
                $data['additionalData'] ?? null,
                $data['lon'] ?? null,
                $data['lat'] ?? null
            );
            return new JsonResponse(['message' => 'Structure updated successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/{id}', name: 'delete_structure', methods: ['DELETE'])]
    public function deleteStructure(int $id, StructureRepository $structureRepository): JsonResponse
    {
        try {
            $structureRepository->deleteStructureById($id);
            return new JsonResponse(['message' => 'Structure deleted successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
