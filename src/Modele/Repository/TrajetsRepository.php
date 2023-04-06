<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Trajets;
use PDO;

class TrajetsRepository extends AbstractRepository implements TrajetsRepositoryInterface {

    public function __construct(ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees) {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string {
        return "nalixt.trajets";
    }

    protected function getNomClePrimaire(): string {
        return "idTrajet";
    }

    protected function getNomsColonnes(): array {
        return ["idTrajet", "trajets"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject {
        return new Trajets($objetFormatTableau["idtrajet"],
                            $objetFormatTableau["trajets"]);
    }

    public function getHistory($login): array {

        $requeteSQL = <<<SQL
            SELECT idtrajet, array_agg(DISTINCT nom_comm) as trajets FROM
                (SELECT unnest(nalixt.historique.historique[(cardinality(nalixt.historique.historique)-9):(cardinality(nalixt.historique.historique))])
                            as id FROM nalixt.historique
                 WHERE login = :login)
                    as h
                    JOIN nalixt.trajets t ON h.id = t.idtrajet
                    JOIN unnest(t.trajets) as tt ON true
                    JOIN nalixt.noeud_routier nr ON (tt)::int = nr.gid
                    JOIN nalixt.noeud_commune nc on nr.insee_comm = nc.insee_comm
            GROUP BY idtrajet;
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            'login' => $login
        ));
        $objets = [];
        foreach ($pdoStatement->fetchAll(PDO::FETCH_ASSOC) as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    public function getTrajet($idTrajet): string {

        $requeteSQL = <<<SQL
            SELECT json 
            FROM nalixt.trajets
            WHERE idtrajet = :idtrajet;
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            'idtrajet' => $idTrajet
        ));
        $result = $pdoStatement->fetch();
        return $result['json'];
    }
}