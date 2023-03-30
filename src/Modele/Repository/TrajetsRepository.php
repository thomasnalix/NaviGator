<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Trajets;

class TrajetsRepository extends AbstractRepository implements TrajetsRepositoryInterface {

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