<?php

namespace Navigator\Modele\Repository;

use Navigator\Lib\MotDePasse;
use Navigator\Modele\DataObject\Utilisateur;
use PDOException;
use PHPUnit\Util\Json;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface {

    public function __construct(ConnexionBaseDeDonneesInterface $connexion) {
        parent::__construct($connexion);
    }

//    /**
//     * @return Utilisateur[]
//     */
//    public static function getUtilisateurs() : array {
//        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->query("SELECT * FROM utilisateur");
//
//        $utilisateurs = [];
//        foreach($pdoStatement as $utilisateurFormatTableau) {
//            $utilisateurs[] = UtilisateurRepository::construire($utilisateurFormatTableau);
//        }
//
//        return $utilisateurs;
//    }

    public function construireDepuisTableau(array $utilisateurTableau): Utilisateur {
        return new Utilisateur(
            $utilisateurTableau["login"],
            $utilisateurTableau["nom"],
            $utilisateurTableau["prenom"],
            $utilisateurTableau["motdepasse"],
            $utilisateurTableau["marque"],
            $utilisateurTableau["modele"]
        );
    }

    public function getNomTable(): string {
        return 'nalixt.utilisateurs';
    }

    public function creer($login, $nom, $prenom, $motDePasse,  $marqueVehicule, $modeleVehicule): Utilisateur {
        return new Utilisateur($login, $nom, $prenom, MotDePasse::hacher($motDePasse), $marqueVehicule, $modeleVehicule);
    }

    public function ajouter(Utilisateur $utilisateur): bool {
        $requeteSQL = <<<SQL
            CALL CREER_UTILISATEUR(
                :login_tag,
                :nom_tag,
                :prenom_tag,
                :motDePasse_tag,
                :maraque_tag,
                :modele_tag
            );
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $values = array(
            "login_tag" => $utilisateur->getLogin(),
            "nom_tag" => $utilisateur->getNom(),
            "prenom_tag" => $utilisateur->getPrenom(),
            "motDePasse_tag" => $utilisateur->getMotDePasse(),
            "maraque_tag" => $utilisateur->getMarqueVehicule(),
            "modele_tag" => $utilisateur->getModeleVehicule()
        );

        // https://localhost/NaviGator/web/inscription?login=max&prenom=Maxence&nom=Tourniayre&email=maxence.tourniayre@gmail.com&mdp=a&mdp2=a&imageProfil&marqueVehicule=Tesla&modeleVehicule=Model x&action=creerDepuisFormulaire&controleur=utilisateur

        try {
            $pdoStatement->execute($values);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public function supprimer(string $login): bool {
        $requeteSQL = <<<SQL
            CALL SUPPRIMER_UTILISATEUR(
                :login
            );
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $values = array(
            ':login' => $login,
        );
        try {
            $pdoStatement->execute($values);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public function mettreAJour($utilisateur): bool {
        $requeteSQL = <<<SQL
            CALL MODIFIER_UTILISATEUR(
                :login_tag,
                :nom_tag,
                :prenom_tag,
                :motDePasse_tag,
                :maraque_tag,
                :modele_tag
            );
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $values = array(
            "login_tag" => $utilisateur->getLogin(),
            "nom_tag" => $utilisateur->getNom(),
            "prenom_tag" => $utilisateur->getPrenom(),
            "motDePasse_tag" => $utilisateur->getMotDePasse(),
            "maraque_tag" => $utilisateur->getMarqueVehicule(),
            "modele_tag" => $utilisateur->getModeleVehicule()
        );
        try {
            $pdoStatement->execute($values);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    protected function getNomClePrimaire(): string {
        return 'login';
    }

    protected function getNomsColonnes(): array {
        return ["login", "nom", "prenom", "motDePasse", "marque", "modele"];
    }
}