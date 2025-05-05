<?php

namespace App\Controller;

use App\Entity\Type;
use App\Entity\Structure;
use App\Entity\Electrical;
use App\Entity\Water;
use App\Entity\User;
use App\Entity\BusStop;
use Doctrine\ORM\EntityManagerInterface;
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
    public function createStructure(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
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
    
        // Créer une instance de la structure en fonction du rôle de l'utilisateur
        $structure = match ($user->getRoles()[0]) {
            'ROLE_EDF' => new Electrical(),
            'ROLE_WATER' => new Water(),
            'ROLE_FILIBUS' => new BusStop(),
            default => new Structure(),
        };
    
        // Définir les propriétés de la structure
        $structure->setName($data['name']);
        $structure->setLocation(new Point((float)$data['lon'], (float)$data['lat']));
        $structure->setCreatedAt();
        $structure->setNetworkId($user->getNetwork());
        $structure->setNetworkTypeId($type);
        $structure->setCreatedBy($user);
    
        // Gérer les champs supplémentaires
        $additionalData = $data['additionalData'] ?? [];
        if ($structure instanceof Electrical && isset($additionalData['capacity'])) {
            $structure->setCapacity($additionalData['capacity']);
        }
        if ($structure instanceof Water) {
            $structure->setWaterPressure($additionalData['water_pressure'] ?? null);
            $structure->setOpen($additionalData['is_open'] ?? true);
        }
        if ($structure instanceof BusStop && isset($additionalData['line_number'])) {
            $structure->setLineNumber($additionalData['line_number']);
        }
    
        try {
            $this->entityManager->persist($structure);
            $this->entityManager->flush();
    
            return new JsonResponse(['success' => true, 'id' => $structure->getId()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'get_structures', methods: ['GET'])]
    public function getStructures(): JsonResponse
    {
        $structures = $this->entityManager->getRepository(Structure::class)->findAll();

        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($structures as $structure) {
            $geoJson['features'][] = [
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
    public function updateStructure(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $structure = $this->entityManager->getRepository(Structure::class)->find($id);

        if (!$structure) {
            return new JsonResponse(['error' => 'Structure not found.'], Response::HTTP_NOT_FOUND);
        }

        $structure->setName($data['name'] ?? $structure->getName());
        $structure->setUpdatedAt();

        try {
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Structure updated successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete_structure', methods: ['DELETE'])]
    public function deleteStructure(int $id): JsonResponse
    {
        $structure = $this->entityManager->getRepository(Structure::class)->find($id);

        if (!$structure) {
            return new JsonResponse(['error' => 'Structure not found.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($structure);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Structure deleted successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
