<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class DatabaseService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function fetchSomething(): array
    {
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT * FROM my_table";
        return $conn->fetchAllAssociative($sql);
    }
}
