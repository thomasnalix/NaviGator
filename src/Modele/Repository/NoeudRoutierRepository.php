<?php

namespace Navigator\Modele\Repository;

use Navigator\Lib\PlusCourtChemin;
use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\NoeudRoutier;
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

    public function calculerItineraire(array $var) {
        $variables = $var;
        // With array, explose all data and put it in a string separated by a comma
        $placeholders = implode(',', array_fill(0, count($variables), '?'));
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare("SELECT geom FROM nalixt.troncon_route WHERE gid IN($placeholders)");
        $pdoStatement->execute($variables);
        $noeudsRoutierRegion = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        $noeudsRoutier = [];
        foreach ($noeudsRoutierRegion as $noeudRoutier) {
            $noeudsRoutier[] = $noeudRoutier['geom'];
        }
        return $noeudsRoutier;
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

    public function getNoeudsRoutierDepartementTime(int $noeudRoutierGid): array {
        $numDepartementNoeudRoutier = $this->getDepartementGid($noeudRoutierGid);
        $requeteSQL = <<<SQL
            SELECT *
            FROM nalixt.vitesses_route_test
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
         * On construit un tableau de noeuds routiers avec leurs voisins tel que :
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
                    "longueur_troncon" => $noeudRoutierRegion["longueur_troncon"],
                    "vitesse" => $noeudRoutierRegion["vitesse"],
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
                    "longueur_troncon" => $noeudRoutierRegion["longueur_troncon"],
                    "vitesse" => $noeudRoutierRegion["vitesse"],
                ];
            }
        }
        return $noeudsRoutierRegionAvecVoisins;
    }

    /**
     * Renvoi les informations d'un noeud routier tel que le gid, et ses coordonnées (lat, long)
     * @param $idRte
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

    public function recupererParGid($gid): ?NoeudRoutier {
        $requeteSQL = <<<SQL
            SELECT gid,
                   ST_X(ST_AsText(geom)) as long,
                   ST_Y(ST_AsText(geom)) as lat
            FROM nalixt.noeud_routier
            WHERE gid = :gid;
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gid" => $gid
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

    public function getNomCommunes($substring) {
        $requeteSQL = <<<SQL
            SELECT insee_comm, nom_comm
            FROM nalixt.noeud_commune
            WHERE LOWER(nom_comm) LIKE LOWER(:substring)
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "substring" => "%$substring%"
        ));
        $objetFormatTableau = $pdoStatement->fetchAll();
        $communes = [];
        foreach ($objetFormatTableau as $commune)
            $communes[] = $commune["nom_comm"] . " (" . $commune["insee_comm"] . ")";
        return $communes;
    }
}
