<?php

namespace Navigator\Service;


interface NoeudRoutierServiceInterface {

    public function getNoeudRoutierProche(float $lat, float $long): array;

    public function calculChemin(int $nbField, array $communesList): array;

}