<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\Trajets;

class TrajetsRepository extends AbstractRepository
{


    protected function getNomTable(): string
    {
        return "nalixt.trajets";
    }

    protected function getNomClePrimaire(): string
    {
        return "idTrajet";
    }

    protected function getNomsColonnes(): array
    {
        return ["idTrajet", "trajets"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Trajets( $objetFormatTableau["idTrajet"],
                            $objetFormatTableau["trajets"]);
    }
}