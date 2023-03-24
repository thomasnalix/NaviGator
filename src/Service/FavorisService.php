<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\Repository\FavorisRepository;

class FavorisService
{

    public function recupererFavoris(): array
    {
        return (new FavorisRepository())->recuperer();
    }

}