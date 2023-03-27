<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\FavorisRepository;

class FavorisService
{

    public function recupererFavoris(): array
    {
        return (new FavorisRepository())->recuperer();
    }

}