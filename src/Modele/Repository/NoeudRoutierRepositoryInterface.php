<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\NoeudRoutier;

interface NoeudRoutierRepositoryInterface {

    public function calculerItineraire(array $tronconsGid): array;

    public function getNoeudsRoutierDepartement(int $numDepartement): array;

    public function recupererNoeudRoutier($idRte): ?NoeudRoutier;

    public function recupererParGid(int $gid): ?NoeudRoutier;

    public function getNoeudProche(float $lat, float $long);

    public function getCoordNoeudByGid(int $gid);
}