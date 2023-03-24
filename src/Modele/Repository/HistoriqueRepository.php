<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\Historique;

class HistoriqueRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "nalixt.historique";
    }

    protected function getNomClePrimaire(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "historique"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Historique( $objetFormatTableau["login"],
                               $objetFormatTableau["historique"]);
    }
}