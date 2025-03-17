<?php

namespace App\Service;

use App\Repository\NetworksRepository;

class NetworkService
{
    private NetworksRepository $networksRepository;

    public function __construct(NetworksRepository $networksRepository)
    {
        $this->networksRepository = $networksRepository;
    }

    public function getNetworkNames(): array
    {
        return $this->networksRepository->findAllNetworkNames();
    }
}
