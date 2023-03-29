<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\NoeudRoutier;

interface NoeudRoutierRepositoryInterface {

    public function calculerItineraire(array $tronconsGid): array;

    public function getNoeudsRoutierDepartement(int $noeudRoutierGid): array;

    public function recupererNoeudRoutier($idRte): ?NoeudRoutier;

    public function recupererParGid(int $gid): ?NoeudRoutier;

    public function getDepartementGid(int $noeudRoutierGid);

    public function getNoeudProche(float $lat, float $long);
}