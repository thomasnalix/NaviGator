<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\NoeudRoutier;

interface NoeudRoutierRepositoryInterface {

    public function calculerItineraire(array $var);

    public function getNoeudsRoutierDepartementTime(int $noeudRoutierGid): array;

    public function recupererNoeudRoutier($idRte): ?NoeudRoutier;

    public function recupererParGid($gid): ?NoeudRoutier;

    public function getDepartementGid($noeudRoutierGid);

    public function getNoeudProche(float $lat, float $long);
}