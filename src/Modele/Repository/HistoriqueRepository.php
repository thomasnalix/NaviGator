<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Historique;

class HistoriqueRepository extends AbstractRepository implements HistoriqueRepositoryInterface {

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