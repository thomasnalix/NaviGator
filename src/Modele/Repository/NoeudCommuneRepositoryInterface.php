<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\NoeudCommune;

interface NoeudCommuneRepositoryInterface {

    public function getCoordNoeudCommune(string $nomVille): array;

    public function getNomCommunes(string $nomCommune): array;

    public function getCommune(string $nomCommune): NoeudCommune;
}