<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Trajets;
use PDO;
use PDOException;

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

    public function getHistory($login): ?array {

        $requeteSQL = <<<SQL
            SELECT t.idtrajet, array_agg(nc.nom_comm ORDER BY t.idtrajet) as trajets
            FROM nalixt.historique h
            JOIN nalixt.trajets t ON t.idtrajet = ANY(h.historique)
            JOIN unnest(t.trajets) as tt ON true
            JOIN nalixt.noeud_routier nr ON (tt)::int = nr.gid
            JOIN nalixt.noeud_commune nc on nr.insee_comm = nc.insee_comm
            WHERE h.login = :login
            GROUP BY t.idtrajet
            ORDER BY t.idtrajet DESC;

        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        try {
            $pdoStatement->execute(array(
                'login' => $login
            ));
        } catch (PDOException) {
            return null;
        }
        $objets = [];
        foreach ($pdoStatement->fetchAll(PDO::FETCH_ASSOC) as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    public function getTrajet($idTrajet): string {

        $requeteSQL = <<<SQL
            SELECT jsonb_set(json, '{noeudsList}', jsonb_agg(jsonb_build_object('gid', g, 'nomCommune', nc.nom_comm) ORDER BY idx)) as json
            FROM nalixt.trajets t
            JOIN jsonb_array_elements_text(json->'noeudsList') WITH ORDINALITY as elem(g, idx) ON true
            JOIN nalixt.noeud_routier nr ON g::int = nr.gid
            JOIN nalixt.noeud_commune nc on nr.insee_comm = nc.insee_comm
            WHERE t.idtrajet = :idtrajet
            GROUP BY t.idtrajet, json;
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            'idtrajet' => $idTrajet
        ));
        $result = $pdoStatement->fetch();
        return $result['json'];
    }
}