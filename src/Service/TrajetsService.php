<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\TrajetsRepositoryInterface;
use Navigator\Service\Exception\ServiceException;

class TrajetsService implements TrajetsServiceInterface {

    public function __construct(private TrajetsRepositoryInterface $trajetsRepository) {}


    /**
     * @throws ServiceException
     */
    public function getHistory($login):array {
        $result = $this->trajetsRepository->getHistory($login);
        if ($result == null) {
            throw new ServiceException("Erreur lors de la récupération de l'historique");
        }
        return $result;
    }
}