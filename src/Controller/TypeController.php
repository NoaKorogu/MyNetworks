<?php

namespace App\Controller;

use App\Entity\Type;
use App\Entity\Networks;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/types')]
class TypeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Récupérer les types associés au réseau de l'utilisateur connecté.
     */
    #[Route('', name: 'get_structure_types', methods: ['GET'])]
    public function getStructureTypes(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $network = $user->getNetwork();
        if (!$network) {
            return new JsonResponse(['error' => 'User is not associated with a network.'], Response::HTTP_BAD_REQUEST);
        }

        $types = $this->entityManager->getRepository(Type::class)->findBy(['network' => $network]);

        $typeData = array_map(fn($type) => [
            'id' => $type->getId(),
            'name' => $type->getName(),
        ], $types);

        return new JsonResponse($typeData);
    }

    /**
     * Ajouter un nouveau type.
     */
    #[Route('/add', name: 'add_type', methods: ['POST'])]
    public function addType(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['networkId'])) {
            return new JsonResponse(['error' => 'Invalid data.'], Response::HTTP_BAD_REQUEST);
        }

        $network = $this->entityManager->getRepository(Networks::class)->find($data['networkId']);
        if (!$network) {
            return new JsonResponse(['error' => 'Network not found.'], Response::HTTP_BAD_REQUEST);
        }

        $type = new Type();
        $type->setName($data['name']);
        $type->setNetwork($network);

        try {
            $this->entityManager->persist($type);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'id' => $type->getId()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprimer un type par son ID.
     */
    #[Route('/{id}', name: 'delete_type', methods: ['DELETE'])]
    public function deleteType(int $id): JsonResponse
    {
        $type = $this->entityManager->getRepository(Type::class)->find($id);

        if (!$type) {
            return new JsonResponse(['error' => 'Type not found.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($type);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'Type deleted successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}