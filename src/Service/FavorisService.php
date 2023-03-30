<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\FavorisRepositoryInterface;

class FavorisService implements FavorisServiceInterface {

    private FavorisRepositoryInterface $favorisRepository;

    public function __construct(FavorisRepositoryInterface $favorisRepository) {
        $this->favorisRepository = $favorisRepository;
    }

    public function recupererFavoris(): array {
        return $this->favorisRepository->recuperer();
    }

}