<?php

namespace App\Controller;

use App\Service\NetworkService;
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

    #[Route('/networks/names', name: 'get_network_names', methods: ['GET'])]
    public function getNetworkNames(): JsonResponse
    {
        $networkNames = $this->networkService->getNetworkNames();
        return $this->json($networkNames);
    }
}
