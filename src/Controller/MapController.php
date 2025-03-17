<?php

namespace App\Controller;

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
}
