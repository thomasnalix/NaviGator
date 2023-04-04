<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\HistoriqueRepositoryInterface;
use Navigator\Service\Exception\ServiceException;

class HistoriqueService implements HistoriqueServiceInterface {

    private HistoriqueRepositoryInterface $historiqueRepository;

    public function __construct(HistoriqueRepositoryInterface $historiqueRepository) {
        $this->historiqueRepository = $historiqueRepository;
    }

    public function recupererHistorique(): array {
        return $this->historiqueRepository->recuperer();
    }

    /**
     * @throws ServiceException
     */
    public function ajouterTrajet($login, $trajet, $json): void {

        $result = $this->historiqueRepository->ajouterHistorique($login, $trajet, $json);
        if (!$result) {
            throw new ServiceException("Erreur lors de l'ajout du trajet à l'historique");
        }
    }
}