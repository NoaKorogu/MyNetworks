<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DatabaseService
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function logAction(string $tableName, int $idElement, array $oldData, array $newData): void
    {
        // Get the currently authenticated user from the token
        $token = $this->tokenStorage->getToken();
        $currentUser = $token ? $token->getUser() : null;
        
        if (!$currentUser) {
            throw new \RuntimeException('No authenticated user found.');
        }

        $log = new Log();
        $log->createNewLog(
            $tableName,
            $idElement,
            $oldData,
            $newData,
            $currentUser
        );

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
