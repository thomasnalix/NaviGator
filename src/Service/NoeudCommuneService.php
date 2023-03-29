<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class NoeudCommuneService implements NoeudCommuneServiceInterface {

    private NoeudCommuneRepositoryInterface $noeudCommuneRepository;

    public function __construct(NoeudCommuneRepositoryInterface $noeudCommuneRepository) {
        $this->noeudCommuneRepository = $noeudCommuneRepository;
    }

    /**
     * @throws ServiceException
     */
    public function getCoordNoeudCommune(string $nomVille): array {

        $result = $this->noeudCommuneRepository->getCoordNoeudCommune($nomVille);
        if ($result == null) {
            throw new ServiceException("Coord noeudCommune not found",Response::HTTP_BAD_REQUEST);
        }
        return $result;
    }

    /**
     * @throws ServiceException
     */
    public function getNomCommunes(string $nomCommune): array {
        return $this->noeudCommuneRepository->getNomCommunes($nomCommune);
    }

}