<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use PDO;

class NoeudRoutierRepository extends AbstractRepository
{

    public function construireDepuisTableau(array $noeudRoutierTableau): NoeudRoutier
    {
        return new NoeudRoutier(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["coords"]
        //$noeudRoutierTableau["id_rte500"],
        );
    }


    protected function getNomTable(): string
    {
        return 'nalixt.noeud_routier';
    }

    protected function getNomClePrimaire(): string
    {
        return 'gid';
    }

    protected function getNomsColonnes(): array
    {
        return ["gid"]; // "id_rte500"
    }

    // On bloque l'ajout, la màj et la suppression pour ne pas modifier la table
    // Normalement, j'ai restreint l'accès à SELECT au niveau de la BD
    public function supprimer(string $valeurClePrimaire): bool
    {
        return false;
    }

    public function mettreAJour(AbstractDataObject $object): void
    {
        return;
    }

    public function ajouter(AbstractDataObject $object): bool
    {
        return false;
    }

    /**
     * Renvoie le tableau des voisins d'un noeud routier
     *
     * Chaque voisin est un tableau avec les 3 champs
     * `noeud_routier_gid`, `troncon_gid`, `longueur`
     *
     * @param int $noeudRoutierGid
     * @return String[][]
     **/
//    public function getVoisins(int $noeudRoutierGid): array
//    {
//
//        $requeteSQL = <<<SQL
//            SELECT noeud_routier_gid_2 as noeud_routier_gid, troncon_gid, longueur
//            FROM nalixt.calcul_noeud_troncon
//            WHERE noeud_routier_gid = :gidTag;
//        SQL;
//        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
//        $pdoStatement->execute(array(
//            "gidTag" => $noeudRoutierGid
//        ));
//
//        return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
//    }


    /**
     * @param int $noeudRoutierGid
     * @return une array avec en clé un noeud routier et en valeur un tableau de voisins
     * TODO: explorer la piste des groupes avec un GROUP BY côté SQL pour accélérer le traitement en PHP ?
     */
    public function getNoeudsRoutierRegion(int $noeudRoutierGid) : array {
        $requeteSQL = <<<SQL
            SELECT * FROM nalixt.calcul_noeud_troncon
            WHERE num_departement = (SELECT num_departement FROM nalixt.calcul_noeud_troncon
            WHERE noeud_courant_gid = :gidTag LIMIT 1);
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $now = microtime(true);
        $pdoStatement->execute(array(
            "gidTag" => $noeudRoutierGid
        ));
        $noeudsRoutierRegion = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        echo "Temps de récupération des noeuds routiers de la région: " . (microtime(true) - $now) . "s<br>";
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
        $now = microtime(true);
        $noeudsRoutierRegionAvecVoisins = [];
        foreach ($noeudsRoutierRegion as $noeudRoutierRegion) {
            $noeudCourantGid = $noeudRoutierRegion["noeud_courant_gid"];
            $noeudVoisinGid = $noeudRoutierRegion["noeud_voisin_gid"];
            $noeudVoisinCoord = $noeudRoutierRegion["noeud_voisin_coord"];
            $tronconGid = $noeudRoutierRegion["troncon_gid"];
            $tronconCoord = $noeudRoutierRegion["troncon_coord"];
            $longueurTroncon = $noeudRoutierRegion["longueur_troncon"];
            $numDepartement = $noeudRoutierRegion["num_departement"];
            $noeudsRoutierRegionAvecVoisins[$numDepartement][$noeudCourantGid][] = [
                "noeud_gid" => $noeudVoisinGid,
                "noeud_coord" => $noeudVoisinCoord,
                "troncon_gid" => $tronconGid,
                "troncon_coord" => $tronconCoord,
                "longueur_troncon" => $longueurTroncon,
            ];
        }
        echo "Temps de construction du tableau de noeuds routiers avec leurs voisins: " . (microtime(true) - $now) . "s<br>";
        return $noeudsRoutierRegionAvecVoisins;
    }


    /**
     * Renvoi un noeud routier avec ses voisins
     * @param int $noeudRoutierGidCourant
     * @return NoeudRoutier
     */
    public function getNoeudRoutier(int $noeudRoutierGidCourant): NoeudRoutier
    {
        $requeteSQL = <<<SQL
            SELECT noeud_routier_gid_2 as noeud_routier_gid, troncon_gid, longueur, troncon_coord, noeud_coord, noeud_voisin_coord
            FROM nalixt.calcul_noeud_troncon
            WHERE noeud_courant = :gidTag;
        SQL;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $noeudRoutierGidCourant
        ));

        $voisins = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        return new NoeudRoutier($noeudRoutierGidCourant, $voisins[0]['noeud_coord'], $voisins);
    }


    /**
     * Renvoi les informations d'un noeud routier tel que le gid, et ses coordonnées (lat, long)
     * @return NoeudRoutier|null
     */
    public function recupererNoeudRoutier($idRte): ?NoeudRoutier
    {
        $requeteSQL = <<<SQL
            SELECT gid,
                   concat(ST_X(ST_AsText(geom)), ';',
                ST_Y(ST_AsText(geom))) as coords
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
}
