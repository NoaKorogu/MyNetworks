<?php

namespace App\Controller;

use App\Repository\TypeRepository;
use App\Repository\PathRepository;
use App\Repository\NetworksRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MapController extends AbstractController
{
    #[Route('/', name: 'app_map')]
    public function index(): Response
    {
        return $this->render('Map/map.html.twig');
    }

    #[Route('/api/networks', name: 'api_networks', methods: ['GET'])]
    public function getNetworks(NetworksRepository $networksRepository): JsonResponse
    {
        $networkNames = $networksRepository->findAllNetworkNames();
        return $this->json($networkNames);
    }

    #[Route('/api/types', name: 'api_types', methods: ['GET'])]
    public function getTypes(TypeRepository $typesRepository): JsonResponse
    {
        $types = $typesRepository->findAllStructureType();
        return $this->json($types);
    }

    // #[Route('/api/paths', name: 'api_paths', methods: ['GET'])]
    // public function getPaths(PathRepository $pathRepository): JsonResponse
    // {
    //     $paths = $pathRepository->findAllPaths();

    //     $geoJson = [
    //         'type' => 'FeatureCollection',
    //         'features' => [],
    //     ];

    //     foreach ($paths as $path) {
    //         $geoJson['features'][] = [
    //             'type' => 'Feature',
    //             'properties' => [
    //                 'id' => $path['id'],
    //                 'name' => $path['name'],
    //                 'color' => $path['color'],
    //             ],
    //             'geometry' => json_decode($path['path_geojson'], true), // Convertir le GeoJSON en tableau PHP
    //         ];
    //     }

    //     return new JsonResponse($geoJson);
    // }

}
