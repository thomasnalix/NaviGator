<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Historique;
use PDOException;

class HistoriqueRepository extends AbstractRepository implements HistoriqueRepositoryInterface {

    public function __construct(ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees) {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string {
        return "nalixt.historique";
    }

    protected function getNomClePrimaire(): string {
        return "login";
    }

    protected function getNomsColonnes(): array {
        return ["login", "historique"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject {
        return new Historique($objetFormatTableau["login"],
            $objetFormatTableau["historique"]);
    }

    public function ajouterHistorique(string $login, string $trajet, string $json): bool {

        // trad string $trajet to postgree array type
        $trajet = "{" . implode(",", explode(",", $trajet)) . "}";
        echo $trajet;
//        $trajet = "ARRAY['Paris', 'Montpellier']";
//        $json="'[\"client\"]'";


        $requeteSQL = <<<SQL
            CALL AJOUTER_HISTORIQUE(
                :login,
                :trajetArray,
                :json
            );
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $values = array(
            ':login' => $login,
            ':trajetArray' => $trajet,
            ':json' => $json
        );
        try {
            $pdoStatement->execute($values);
            return true;
        } catch (PDOException) {
            var_dump($pdoStatement->errorInfo());
            return false;
        }
    }
}