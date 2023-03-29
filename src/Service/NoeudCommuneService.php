<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;

class NoeudCommuneService implements NoeudCommuneServiceInterface {

    private NoeudCommuneRepositoryInterface $noeudCommuneRepository;

    public function __construct(NoeudCommuneRepositoryInterface $noeudCommuneRepository) {
        $this->noeudCommuneRepository = $noeudCommuneRepository;
    }

    public function getNoeudCommuneProche(float $lat, float $long): array {
        return $this->noeudCommuneRepository->getNoeudProche($lat, $long);
    }

    public function getCoordNoeudCommune(string $nomVille): array {
        return $this->noeudCommuneRepository->getCoordNoeudCommune($nomVille);
    }

    public function getNomCommunes(string $nomCommune): array {
        return $this->noeudCommuneRepository->getNomCommunes($nomCommune);
    }

}