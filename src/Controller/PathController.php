<?php

namespace App\Controller;

use App\Entity\Path;
use App\Repository\PathRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/paths')]
class PathController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'create_path', methods: ['POST'])] //It works
    public function createWay(Request $request, PathRepository $pathRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['color'], $data['coordinates']) || count($data['coordinates']) < 2) {
            return new JsonResponse(['error' => 'Invalid data.'], Response::HTTP_BAD_REQUEST);
        }

        $networkId = $data['networkId'] ?? null;

        try {
            $pathRepository->createPath($data['name'], $data['color'], $data['coordinates'], $networkId);
            return new JsonResponse(['message' => 'Path created successfully.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'api_paths', methods: ['GET'])] // It works
    public function getPaths(PathRepository $pathRepository): JsonResponse
    {
        $paths = $pathRepository->findAllPaths();

        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($paths as $path) {
            $geoJson['features'][] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $path['id'],
                    'name' => $path['name'],
                    'color' => $path['color'],
                ],
                'geometry' => json_decode($path['path_geojson'], true),
            ];
        }

        return new JsonResponse($geoJson);
    }

    #[Route('/last', name: 'get_last_path', methods: ['GET'])]
    public function getLastPath(PathRepository $pathRepository): JsonResponse
    {
        $path = $pathRepository->findLastPath();

        if (!$path) {
            return new JsonResponse(['error' => 'No paths found.'], Response::HTTP_NOT_FOUND);
        }

        $geoJson = [
            'type' => 'Feature',
            'properties' => [
                'id' => $path[0]['id'],
                'name' => $path[0]['name'],
                'color' => $path[0]['color'],
            ],
            'geometry' => json_decode($path[0]['path_geojson'], true),
        ];

        return new JsonResponse($geoJson);
    }

    #[Route('/{id}', name: 'get_path', methods: ['GET'])] //It works
    public function getPath(PathRepository $pathRepository, int $id): JsonResponse
    {
        $path = $pathRepository->findPathById($id);

        if (!$path) {
            return new JsonResponse(['error' => 'Path not found.'], Response::HTTP_NOT_FOUND);
        }

        $geoJson = [
            'type' => 'Feature',
            'properties' => [
                'id' => $path[0]['id'],
                'name' => $path[0]['name'],
                'color' => $path[0]['color'],
            ],
            'geometry' => json_decode($path[0]['path_geojson'], true),
        ];

        return new JsonResponse($geoJson);
    }

    #[Route('/{id}', name: 'update_path', methods: ['PUT'])]
    public function updatePath(Request $request, int $id, PathRepository $pathRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Extract name and color from the request body
        $name = $data['name'] ?? null;
        $color = $data['color'] ?? null;
    
        // Call the repository method to update the path
        try {
            $pathRepository->updatePath($id, $name, $color);
            return new JsonResponse(['message' => 'Path updated successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete_path', methods: ['DELETE'])] //It works but soft delete only the path withoout deleting the path in the collection of path in Networks and PartOf entity
    public function deletePath(int $id, PathRepository $pathRepository): JsonResponse
    {
        try {
            $pathRepository->deletePathById($id);

            return new JsonResponse(['message' => 'Path deleted successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
