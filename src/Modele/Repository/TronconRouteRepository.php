<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\TronconRoute;

class TronconRouteRepository extends AbstractRepository {

    public function construireDepuisTableau(array $noeudRoutierTableau): TronconRoute {
        return new TronconRoute(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["id_rte500"],
            $noeudRoutierTableau["sens"],
            $noeudRoutierTableau["num_route"] ?? "",
            (float) $noeudRoutierTableau["longueur"],
            null
        );
    }

    protected function getNomTable(): string {
        return 'nalixt.troncon_route';
    }

    protected function getNomClePrimaire(): string {
        return 'gid';
    }

    protected function getNomsColonnes(): array {
        return ["gid", "id_rte500", "sens", "num_route", "longueur"];
    }

    // On bloque l'ajout, la màj et la suppression pour ne pas modifier la table
    // Normalement, j'ai restreint l'accès à SELECT au niveau de la BD
    public function supprimer(string $valeurClePrimaire): bool {
        return false;
    }

    public function mettreAJour(AbstractDataObject $object): void {
        return;
    }

    public function ajouter(AbstractDataObject $object): bool {
        return false;
    }

}
