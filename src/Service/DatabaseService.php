<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class DatabaseService
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function logAction(string $tableName, int $idElement, array $oldData, array $newData): void
    {
        $currentUser = $this->security->getUser(); // Get the currently authenticated user
        // $currentUser = $this->entityManager->getRepository(User::class)->find(1); // Replace with actual user retrieval logic

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
