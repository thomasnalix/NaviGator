<?php

namespace Navigator\Service;

use Navigator\Lib\PlusCourtChemin;
use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Modele\Repository\NoeudRoutierRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class NoeudRoutierService implements NoeudRoutierServiceInterface {

    private NoeudRoutierRepositoryInterface $noeudRoutierRepository;
    private NoeudCommuneRepositoryInterface $noeudCommuneRepository;

    public function __construct(
        NoeudRoutierRepositoryInterface $noeudRoutierRepository,
        NoeudCommuneRepositoryInterface $noeudCommuneRepository
    ) {
        $this->noeudRoutierRepository = $noeudRoutierRepository;
        $this->noeudCommuneRepository = $noeudCommuneRepository;
    }

    /**
     * @throws ServiceException
     */
    public function getNoeudRoutierProche(float $lat, float $long): array {
        $result = $this->noeudRoutierRepository->getNoeudProche($lat, $long);
        if ($result == null) {
            throw new ServiceException("Noeud routier not found",Response::HTTP_BAD_REQUEST);
        }
        return $result;
    }

    /**
     * @throws ServiceException
     */
    public function getVillesItinary(int $nbField, array $communesList): array {
        $noeudRoutier = [];
        foreach ($communesList as $key => $value) {
            if (str_starts_with($key, 'gid')) {
                $noeudRoutier[] = $this->noeudRoutierRepository->recupererParGid($value);
            } else {
                $noeudCommune = $this->noeudCommuneRepository->getCommune($value);
                $noeudRoutier[] = $this->noeudRoutierRepository->recupererNoeudRoutier($noeudCommune->getId_nd_rte());
            }
        }
        return $noeudRoutier;
    }

    public function calculerItineraire(array $tronconsGid): array {
        if (count($tronconsGid) == 0)
            throw new ServiceException("Error while calculating the path",Response::HTTP_BAD_REQUEST);
        return $this->noeudRoutierRepository->calculerItineraire($tronconsGid);
    }

    public function getNoeudsRoutierDepartement(int $noeudRoutierGid): array {
        return $this->noeudRoutierRepository->getNoeudsRoutierDepartement($noeudRoutierGid);
    }
}