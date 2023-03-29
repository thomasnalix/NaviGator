<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\NoeudCommune;
use PDO;

class NoeudCommuneRepository extends AbstractRepository implements NoeudCommuneRepositoryInterface {

    private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees;

    public function __construct(ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees) {
        $this->connexionBaseDeDonnees = $connexionBaseDeDonnees;
    }

    public function construireDepuisTableau(array $noeudRoutierTableau): NoeudCommune {
        return new NoeudCommune(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["id_rte500"],
            $noeudRoutierTableau["nom_comm"],
            $noeudRoutierTableau["id_nd_rte"]
        );
    }

    protected function getNomTable(): string { return 'nalixt.noeud_commune';}

    protected function getNomClePrimaire(): string { return 'gid'; }

    protected function getNomsColonnes(): array {
        return ["gid", "id_rte500", "nom_comm", "id_nd_rte"];
    }

    public function getCoordNoeudCommune(string $nomVille) : ?array {
        $sql = <<<SQL
            SELECT nr.lat, nr.long
            FROM nalixt.noeud_routier nr
            JOIN nalixt.noeud_commune nc ON nr.id_rte500 = nc.id_nd_rte
            WHERE nom_comm = :nomVille
            LIMIT 1;
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            "nomVille" => $nomVille
        ]);
        return $pdoStatement->fetch(PDO::FETCH_ASSOC) ?? null;
    }

    public function getNomCommunes($substring): array {
        $requeteSQL = <<<SQL
            SELECT insee_comm, nom_comm
            FROM nalixt.noeud_commune
            WHERE LOWER(nom_comm) LIKE LOWER(:substring)
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "substring" => "%$substring%"
        ));
        $objetFormatTableau = $pdoStatement->fetchAll();
        $communes = [];
        foreach ($objetFormatTableau as $commune)
            $communes[] = $commune["nom_comm"] . " (" . $commune["insee_comm"] . ")";
        return $communes;
    }

    public function getCommune(string $nomCommune): ?NoeudCommune {
        $request = <<<SQL
            SELECT gid, id_rte500, nom_comm, id_nd_rte 
            FROM nalixt.noeud_commune 
            WHERE nom_comm = :nomCommune
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($request);
        $pdoStatement->execute([
            "nomCommune" => $nomCommune
        ]);
        $objetFormatTableau = $pdoStatement->fetch();
        return $this->construireDepuisTableau($objetFormatTableau) ?? null;
    }
}
