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
            $noeudDepartGid = $noeudRoutierRegion["noeud_depart_gid"];
            $noeudDepartLat = $noeudRoutierRegion["noeud_depart_lat"];
            $noeudDepartLong = $noeudRoutierRegion["noeud_depart_long"];
            $noeudArriveeGid = $noeudRoutierRegion["noeud_arrivee_gid"];
            $noeudArriveeLat = $noeudRoutierRegion["noeud_arrivee_lat"];
            $noeudArriveeLong = $noeudRoutierRegion["noeud_arrivee_long"];
            $tronconGid = $noeudRoutierRegion["troncon_gid"];
            $tronconCoord = $noeudRoutierRegion["troncon_coord"];
            $longueurTroncon = $noeudRoutierRegion["longueur_troncon"];
            $numDepartementDepart = $noeudRoutierRegion["num_departement_depart"];
            $numDepartementArrivee = $noeudRoutierRegion["num_departement_arrivee"];


            // en fonction de $numDepartementNoeudRoutier, on va ajouter dans le tableau avec les bonnes valeurs donc noeudArrive ou noeudArrive
            if ($numDepartementNoeudRoutier === $numDepartementDepart) {
                $noeudsRoutierRegionAvecVoisins[$numDepartementNoeudRoutier][$noeudDepartGid][] = [
                    "noeud_gid" => $noeudArriveeGid,
                    "noeud_courant_lat" => $noeudDepartLat,
                    "noeud_courant_long" => $noeudDepartLong,
                    "noeud_coord_lat" => $noeudArriveeLat,
                    "noeud_coord_long" => $noeudArriveeLong,
                    "troncon_gid" => $tronconGid,
                    "troncon_coord" => $tronconCoord,
                    "longueur_troncon" => $longueurTroncon,
                ];
            }
            if ($numDepartementNoeudRoutier === $numDepartementArrivee) {
                $noeudsRoutierRegionAvecVoisins[$numDepartementNoeudRoutier][$noeudArriveeGid][] = [
                    "noeud_gid" => $noeudDepartGid,
                    "noeud_courant_lat" => $noeudArriveeLat,
                    "noeud_courant_long" => $noeudArriveeLong,
                    "noeud_coord_lat" => $noeudDepartLat,
                    "noeud_coord_long" => $noeudDepartLong,
                    "troncon_gid" => $tronconGid,
                    "troncon_coord" => $tronconCoord,
                    "longueur_troncon" => $longueurTroncon,
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
