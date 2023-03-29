<?php

namespace Navigator\Service;

interface NoeudCommuneServiceInterface {
    public function getNoeudCommuneProche(float $lat, float $long): array;

    public function getCoordNoeudCommune(string $nomVille): array;

    public function getNomCommunes(string $nomCommune): array;
}