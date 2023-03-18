<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository {
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

    protected function getNomClePrimaire(): string {
        return 'login';
    }

    protected function getNomsColonnes(): array {
        return ["login", "nom", "prenom", "motDePasse", "email", "imageProfil"];
    }
}