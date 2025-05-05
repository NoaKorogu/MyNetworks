<?php

namespace App\Controller;

use App\Entity\Networks;
use App\Service\NetworkService;
use App\Repository\NetworksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/networks')]
class NetworkController extends AbstractController
{
    private NetworkService $networkService;

    private EntityManagerInterface $entityManager;
    
    public function __construct(NetworkService $networkService, EntityManagerInterface $entityManager)
    {
        $this->networkService = $networkService;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'api_networks', methods: ['GET'])]
    public function getNetworks(NetworksRepository $networksRepository): JsonResponse
    {
        $networkNames = $networksRepository->findAllNetworkNames();
        return $this->json($networkNames);
    }
    #[Route('/add', name: 'add_network', methods: ['POST'])]
    public function addNetwork(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return new JsonResponse(['error' => 'Invalid data.'], Response::HTTP_BAD_REQUEST);
        }

        $network = new Networks();
        $network->setName($data['name']);

        try {
            $this->entityManager->persist($network);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'id' => $network->getId()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
