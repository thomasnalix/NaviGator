<?php

namespace Navigator\Service;

interface NoeudCommuneServiceInterface {

    public function getCoordNoeudCommune(string $nomVille): array;

    public function getNomCommunes(string $nomCommune): array;

}