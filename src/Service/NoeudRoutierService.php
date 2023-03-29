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
    public function calculChemin(int $nbField, array $communesList): array {

        $noeudRoutier = [];
        foreach ($communesList as $key => $value) {
            // if $key starts with "gid"
            if (str_starts_with($key, 'gid')) {
                $noeudRoutier[] = $this->noeudRoutierRepository->recupererParGid($value);
            } else {
                $noeudCommune = $this->noeudCommuneRepository->getCommune($value);
                $noeudRoutier[] = $this->noeudRoutierRepository->recupererNoeudRoutier($noeudCommune->getId_nd_rte());
            }
        }
        $pcc = new PlusCourtChemin($noeudRoutier, $this->noeudRoutierRepository);
        $datas = $pcc->aStarDistance();
        $parameters["distance"] = $datas[0];
        $parameters["temps"] = $datas[2];
        $parameters["gas"] = $datas[3];

        if ($datas[1] != -1)
            $parameters["chemin"] = $this->noeudRoutierRepository->calculerItineraire($datas[1]);

        $parameters["nbCommunes"] = count($communesList);
        $parameters["nomCommuneDepart"] = array_shift($communesList);
        $parameters["nomCommuneArrivee"] = end($communesList);

        if ($datas[0] == -1)
            throw new ServiceException("Error while calculating the path",Response::HTTP_BAD_REQUEST);

        return $parameters;
    }
}