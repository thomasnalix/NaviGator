<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Lib\PlusCourtChemin;
use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use PDO;

class NoeudRoutierRepository extends AbstractRepository
{

    public function construireDepuisTableau(array $noeudRoutierTableau): NoeudRoutier {
        return new NoeudRoutier(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["lat"],
            $noeudRoutierTableau["long"]
        );
    }


    protected function getNomTable(): string {
        return 'nalixt.noeud_routier';
    }

    protected function getNomClePrimaire(): string {
        return 'gid';
    }

    protected function getNomsColonnes(): array {
        return ["gid"]; // "id_rte500"
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

    /**
     * @param int $noeudRoutierGid
     * @return une array avec en clé un noeud routier et en valeur un tableau de voisins
     * TODO: explorer la piste des groupes avec un GROUP BY côté SQL pour accélérer le traitement en PHP ?
     */
    public function getNoeudsRoutierDepartement(int $noeudRoutierGid) : array {
        $numDepartementNoeudRoutier = $this->getDepartementGid($noeudRoutierGid);
        $requeteSQL = <<<SQL
            --             SELECT *
            --             FROM nalixt.noeuds_from_troncon
            --             WHERE num_departement_depart = (SELECT num_departement
            --             FROM nalixt.noeud_gid_dep
            --             WHERE gid = :gidTag)
            --             OR
            --             num_departement_arrivee = (SELECT num_departement
            --             FROM nalixt.noeud_gid_dep
            --             WHERE gid = :gidTag);
            SELECT *
            FROM nalixt.noeuds_from_troncon
            WHERE num_departement_depart = :departement
            OR
            num_departement_arrivee = :departement;
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
                "departement" => $numDepartementNoeudRoutier
        ));
        $noeudsRoutierRegion = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        /**
         * On récup ça:
         * 1 2 ...
         * 1 3 ...
         * 1 4 ...
         * 2 5 ...
         * 2 6 ...
         * Et construit un tableau de noeuds routiers avec leurs voisins tel que :
         * [
         *     numDepartement => [
         *          1 => [2, 3, 4],
         *          2 => [5, 6],
         *     ],
         *     numDepartement2 => [ ... ],
         * ]
         */
        $noeudsRoutierRegionAvecVoisins = [];

        foreach ($noeudsRoutierRegion as $noeudRoutierRegion) {
            // en fonction de $numDepartementNoeudRoutier, on va ajouter dans le tableau avec les bonnes valeurs donc noeudArrive ou noeudArrive
            if ($numDepartementNoeudRoutier === $noeudRoutierRegion["num_departement_depart"]) {
                $noeudsRoutierRegionAvecVoisins[$numDepartementNoeudRoutier][$noeudRoutierRegion["noeud_depart_gid"]][] = [
                    "noeud_gid" => $noeudRoutierRegion["noeud_arrivee_gid"],
                    "noeud_courant_lat" => $noeudRoutierRegion["noeud_depart_lat"],
                    "noeud_courant_long" => $noeudRoutierRegion["noeud_depart_long"],
                    "noeud_coord_lat" => $noeudRoutierRegion["noeud_arrivee_lat"],
                    "noeud_coord_long" => $noeudRoutierRegion["noeud_arrivee_long"],
                    "troncon_gid" => $noeudRoutierRegion["troncon_gid"],
                    "troncon_coord" => $noeudRoutierRegion["troncon_coord"],
                    "longueur_troncon" => $noeudRoutierRegion["longueur_troncon"],
                ];
            }
            if ($numDepartementNoeudRoutier ===  $noeudRoutierRegion["num_departement_arrivee"]) {
                $noeudsRoutierRegionAvecVoisins[$numDepartementNoeudRoutier][$noeudRoutierRegion["noeud_arrivee_gid"]][] = [
                    "noeud_gid" => $noeudRoutierRegion["noeud_depart_gid"],
                    "noeud_courant_lat" => $noeudRoutierRegion["noeud_arrivee_lat"],
                    "noeud_courant_long" => $noeudRoutierRegion["noeud_arrivee_long"],
                    "noeud_coord_lat" => $noeudRoutierRegion["noeud_depart_lat"],
                    "noeud_coord_long" => $noeudRoutierRegion["noeud_depart_long"],
                    "troncon_gid" => $noeudRoutierRegion["troncon_gid"],
                    "troncon_coord" => $noeudRoutierRegion["troncon_coord"],
                    "longueur_troncon" => $noeudRoutierRegion["longueur_troncon"],
                ];
            }
        }
        return $noeudsRoutierRegionAvecVoisins;
    }

    /**
     * Renvoi les informations d'un noeud routier tel que le gid, et ses coordonnées (lat, long)
     * @return NoeudRoutier|null
     */
    public function recupererNoeudRoutier($idRte): ?NoeudRoutier {
        $requeteSQL = <<<SQL
            SELECT gid,
                   ST_X(ST_AsText(geom)) as long,
                ST_Y(ST_AsText(geom)) as lat
            FROM nalixt.noeud_routier
            WHERE id_rte500 = :idRteTag;
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "idRteTag" => $idRte
        ));
        $objetFormatTableau = $pdoStatement->fetch();
        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    public function getDepartementGid($noeudRoutierGid) {
        $requeteSQL = <<<SQL
            SELECT num_departement
            FROM nalixt.noeud_gid_dep
            WHERE gid = :gid
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gid" => $noeudRoutierGid
        ));
        $objetFormatTableau = $pdoStatement->fetch();
        return $objetFormatTableau["num_departement"];
    }
}
