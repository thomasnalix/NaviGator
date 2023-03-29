<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\isEmpty;

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
        if ($result === null) {
            throw new ServiceException("Coordonnées du noeud introuvable", Response::HTTP_BAD_REQUEST);
        }
        return $result;
    }

    /**
     * @throws ServiceException
     */
    public function getNomCommunes(string $nomCommune): array {
        $nomCommunes = $this->noeudCommuneRepository->getNomCommunes($nomCommune);
        usort($nomCommunes, function ($a, $b) use ($nomCommune) {
            if (str_starts_with($a, $nomCommune) && str_starts_with($b, $nomCommune)) return 0;
            if (str_starts_with($a, $nomCommune)) return -1;
            if (str_starts_with($b, $nomCommune)) return 1;
            return 0;
        });
        if ($nomCommunes === null)
            throw new ServiceException("Nom de commune introuvable", Response::HTTP_BAD_REQUEST);
        return $nomCommunes;
    }

}