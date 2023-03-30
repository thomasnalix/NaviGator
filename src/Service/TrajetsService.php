<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\TrajetsRepositoryInterface;

class TrajetsService implements TrajetsServiceInterface {

    private TrajetsRepositoryInterface $trajetsRepository;

    public function __construct(TrajetsRepositoryInterface $trajetsRepository) {
        $this->trajetsRepository = $trajetsRepository;
    }

    public function recupererTrajets(): array {
        return $this->trajetsRepository->recuperer();
    }
}