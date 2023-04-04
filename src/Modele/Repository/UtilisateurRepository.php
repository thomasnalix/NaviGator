<?php

namespace Navigator\Modele\Repository;

use Navigator\Lib\MotDePasse;
use Navigator\Modele\DataObject\Utilisateur;
use PDOException;

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
            $utilisateurTableau["email"],
            $utilisateurTableau["imageprofil"],
            $utilisateurTableau["marquevehicule"], // TODO : ne fonctionne pas car hstore
            $utilisateurTableau["modelevehicule"]
        );
    }

    public function getNomTable(): string {
        return 'nalixt.utilisateurs';
    }

    public function creer($login, $nom, $prenom, $motDePasse, $email, $imageProfil, $marqueVehicule, $modeleVehicule): Utilisateur {
        return new Utilisateur($login, $nom, $prenom, MotDePasse::hacher($motDePasse), $email, $imageProfil, $marqueVehicule, $modeleVehicule);
    }

    public function ajouter(Utilisateur $utilisateur): bool {
        $requeteSQL = <<<SQL
            CALL CREER_UTILISATEUR(
                :login_tag,
                :nom_tag,
                :prenom_tag,
                :motDePasse_tag,
                :email_tag,
                :imageProfil_tag,
                :vehicule_tag
            );
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $values = array(
            "login_tag" => $utilisateur->getLogin(),
            "nom_tag" => $utilisateur->getNom(),
            "prenom_tag" => $utilisateur->getPrenom(),
            "motDePasse_tag" => $utilisateur->getMotDePasse(),
            "email_tag" => $utilisateur->getEmail(),
            "imageProfil_tag" => $utilisateur->getImageProfil(),
            "vehicule_tag" => "' \"modele\"=>\"" . $utilisateur->getModeleVehicule() . "\", \"marque\"=>\"" . $utilisateur->getMarqueVehicule() . "\" '"
        );
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
                :email_tag,
                :imageProfil_tag,
                :vehicule_tag
            );
        SQL;
        $pdoStatement = $this->connexion->getPdo()->prepare($requeteSQL);
        $values = array(
            "login_tag" => $utilisateur->getLogin(),
            "nom_tag" => $utilisateur->getNom(),
            "prenom_tag" => $utilisateur->getPrenom(),
            "motDePasse_tag" => $utilisateur->getMotDePasse(),
            "email_tag" => $utilisateur->getEmail(),
            "imageProfil_tag" => $utilisateur->getImageProfil(),
            "vehicule_tag" => "' \"modele\"=>\"" . $utilisateur->getModeleVehicule() . "\", \"marque\"=>\"" . $utilisateur->getMarqueVehicule() . "\" '"
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
        return ["login", "nom", "prenom", "motDePasse", "email", "imageProfil", "vehicule"];
    }
}