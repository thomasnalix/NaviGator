<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Favoris;

class FavorisRepository extends AbstractRepository implements FavorisRepositoryInterface {

    public function __construct(ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees) {
        parent::__construct($connexionBaseDeDonnees);
    }

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