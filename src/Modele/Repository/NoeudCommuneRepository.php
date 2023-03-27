<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\NoeudCommune;
use PDO;

class NoeudCommuneRepository extends AbstractRepository
{

    public function construireDepuisTableau(array $noeudRoutierTableau): NoeudCommune {
        return new NoeudCommune(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["id_rte500"],
            $noeudRoutierTableau["nom_comm"],
            $noeudRoutierTableau["id_nd_rte"]
        );
    }

    protected function getNomTable(): string {
        return 'nalixt.noeud_commune';
    }

    protected function getNomClePrimaire(): string {
        return 'gid';
    }

    protected function getNomsColonnes(): array {
        return ["gid", "id_rte500", "nom_comm", "id_nd_rte"];
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

    public function getNoeudProche(float $lat, float $long) {
        $sql = <<<SQL
            SELECT nr.gid, "left"(nr.insee_comm::text, 2) as departement, nom_comm, nr.lat, nr.long
            FROM nalixt.noeud_routier nr
            JOIN nalixt.noeud_commune nc ON nr.insee_comm = nc.insee_comm
            ORDER BY ST_DistanceSphere(ST_SetSRID(ST_MakePoint(:long, :lat), 4326), nr.geom)
            LIMIT 1;
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute([
            "lat" => $lat,
            "long" => $long
        ]);
        $noeudCommune = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        return $noeudCommune;
    }

    public function getCoordNoeudVille(string $nomVille) : array {
        $sql = <<<SQL
            SELECT nr.lat, nr.long
            FROM nalixt.noeud_routier nr
            JOIN nalixt.noeud_commune nc ON nr.id_rte500 = nc.id_nd_rte
            WHERE nom_comm = :nomVille
            LIMIT 1;
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute([
            "nomVille" => $nomVille
        ]);
        return $pdoStatement->fetch(PDO::FETCH_ASSOC);
    }

}
