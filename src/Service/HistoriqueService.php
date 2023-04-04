<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\HistoriqueRepositoryInterface;

class HistoriqueService implements HistoriqueServiceInterface {

    private HistoriqueRepositoryInterface $historiqueRepository;

    public function __construct(HistoriqueRepositoryInterface $historiqueRepository) {
        $this->historiqueRepository = $historiqueRepository;
    }

    public function recupererHistorique(): array {
        return $this->historiqueRepository->recuperer();
    }

    public function ajouterTrajet($login, $trajet, $json) {
        $this->historiqueRepository->ajouterHistorique($login, $trajet, $json);
    }
}