<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\Favoris;

class FavorisRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "nalixt.favoris";
    }

    protected function getNomClePrimaire(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "favoris"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Favoris( $objetFormatTableau["login"],
                            $objetFormatTableau["favoris"]);
    }
}