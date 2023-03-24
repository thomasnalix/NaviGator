<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\Repository\TrajetsRepository;

class TrajetsService
{
    public function recupererTrajets(): array
    {
        return (new TrajetsRepository())->recuperer();
    }
}