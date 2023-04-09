<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\NoeudRoutier;
use PDO;

class NoeudRoutierRepository extends AbstractRepository implements NoeudRoutierRepositoryInterface {


    public function __construct(ConnexionBaseDeDonneesInterface $connexion) {
        parent::__construct($connexion);
    }

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
        return ["gid"];
    }

    public function calculerItineraire(array $tronconsGid): array {
        // With array, explose all data and put it in a string separated by a comma
        $placeholders = implode(',', array_fill(0, count($tronconsGid), '?'));
        $pdoStatement = $this->connexion->getPdo()->prepare("SELECT geom FROM nalixt.troncon_route WHERE gid IN($placeholders)");
        $pdoStatement->execute($tronconsGid);
        $noeudsRoutierRegion = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        $noeudsRoutier = [];
        foreach ($noeudsRoutierRegion as $noeudRoutier) {
            $noeudsRoutier[] = $noeudRoutier['geom'];
        }
        return $noeudsRoutier;
    }

    public function getCoordNoeudByGid(int $gid): ?array {
        $sql = <<<SQL
            SELECT lat, long
            FROM nalixt.noeud_routier
            WHERE gid = :gid
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($sql);
        $pdoStatement->execute([
            "gid" => $gid
        ]);
        return $pdoStatement->fetch(PDO::FETCH_ASSOC) ?? null;
    }

    public function getNoeudsRoutierDepartement(int $noeudRoutierGid): array {
        $numDepartementNoeudRoutier = $this->getDepartementGid($noeudRoutierGid);
        $requeteSQL = <<<SQL
            SELECT *
            FROM nalixt.vitesses_route
            WHERE num_departement_depart = :departement
            OR
            num_departement_arrivee = :departement;
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
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
            if ($numDepartementNoeudRoutier === $noeudRoutierRegion["num_departement_arrivee"]) {
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
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "idRteTag" => $idRte
        ));
        $objetFormatTableau = $pdoStatement->fetch();
        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    public function recupererParGid(int $gid): ?NoeudRoutier {
        $requeteSQL = <<<SQL
            SELECT gid,
                   ST_X(ST_AsText(geom)) as long,
                   ST_Y(ST_AsText(geom)) as lat
            FROM nalixt.noeud_routier
            WHERE gid = :gid;
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gid" => $gid
        ));
        $objetFormatTableau = $pdoStatement->fetch();
        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    public function getDepartementGid(int $noeudRoutierGid) {
        $requeteSQL = <<<SQL
            SELECT num_departement
            FROM nalixt.noeud_gid_dep
            WHERE gid = :gid
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gid" => $noeudRoutierGid
        ));
        $objetFormatTableau = $pdoStatement->fetch();
        return $objetFormatTableau["num_departement"];
    }

    public function getNoeudProche(float $lat, float $long) {
        $sql = <<<SQL
            SELECT nr.gid, "left"(nr.insee_comm::text, 2) as departement, nom_comm, nr.lat, nr.long
            FROM nalixt.noeud_routier nr
            JOIN nalixt.noeud_commune nc ON nr.insee_comm = nc.insee_comm
            ORDER BY ST_DistanceSphere(ST_SetSRID(ST_MakePoint(:long, :lat), 4326), nr.geom)
            LIMIT 1;
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($sql);
        $pdoStatement->execute([
            "lat" => $lat,
            "long" => $long
        ]);
        $noeudCommune = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        return $noeudCommune;
    }


}
