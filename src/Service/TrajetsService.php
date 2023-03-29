<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\TrajetsRepository;

class TrajetsService {
    public function recupererTrajets(): array {
        return (new TrajetsRepository())->recuperer();
    }
}