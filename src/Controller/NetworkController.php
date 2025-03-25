<?php

namespace App\Controller;

use App\Service\NetworkService;
use App\Repository\NetworksRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NetworkController extends AbstractController
{
    private NetworkService $networkService;
    
    public function __construct(NetworkService $networkService)
    {
        $this->networkService = $networkService;
    }

    #[Route('/api/networks', name: 'api_networks', methods: ['GET'])]
    public function getNetworks(NetworksRepository $networksRepository): JsonResponse
    {
        $networkNames = $networksRepository->findAllNetworkNames();
        return $this->json($networkNames);
    }
}
