<?php

namespace Navigator\Modele\Repository;

use Navigator\Lib\MotDePasse;
use Navigator\Modele\DataObject\Utilisateur;
use PDOException;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

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
            $utilisateurTableau["imageprofil"]
        );
    }

    public function getNomTable(): string {
        return 'nalixt.utilisateurs';
    }

    public function creer($login, $nom, $prenom, $motDePasse, $email, $imageProfil): Utilisateur {
        return new Utilisateur($login, $nom, $prenom, MotDePasse::hacher($motDePasse), $email, $imageProfil);
    }

    public function ajouter(Utilisateur $utilisateur): bool {
        $requeteSQL = <<<SQL
            CALL CREER_UTILISATEUR(
                :login,
                :nom,
                :prenom,
                :motDePasse,
                :email,
                :imageProfil
            );
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($requeteSQL);
        $values = array(
            ':login' => $utilisateur->getLogin(),
            ':nom' => $utilisateur->getNom(),
            ':prenom' => $utilisateur->getPrenom(),
            ':motDePasse' => $utilisateur->getMotDePasse(),
            ':email' => $utilisateur->getEmail(),
            ':imageProfil' => $utilisateur->getImageProfil()
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
                :login,
            );
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($requeteSQL);
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
                :login,
                :nom,
                :prenom,
                :motDePasse,
                :email,
                :imageProfil
            );
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($requeteSQL);
        $values = array(
            ':login' => $utilisateur->getLogin(),
            ':nom' => $utilisateur->getNom(),
            ':prenom' => $utilisateur->getPrenom(),
            ':motDePasse' => $utilisateur->getMotDePasse(),
            ':email' => $utilisateur->getEmail(),
            ':imageProfil' => $utilisateur->getImageProfil()
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
        return ["login", "nom", "prenom", "motDePasse", "email", "imageProfil"];
    }
}