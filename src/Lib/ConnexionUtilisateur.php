<?php

namespace Navigator\Lib;

use Navigator\Modele\DataObject\Utilisateur;
use Navigator\Modele\HTTP\Session;
use Navigator\Modele\Repository\UtilisateurRepository;

class ConnexionUtilisateur
{
    private static string $cleConnexion = "_utilisateurConnecte";

    public static function connecter(string $loginUtilisateur): void {
        $session = Session::getInstance();
        $session->enregistrer(ConnexionUtilisateur::$cleConnexion, $loginUtilisateur);
    }

    public static function estConnecte(): bool {
        $session = Session::getInstance();
        return $session->existeCle(ConnexionUtilisateur::$cleConnexion);
    }

    public static function deconnecter() {
        $session = Session::getInstance();
        $session->supprimer(ConnexionUtilisateur::$cleConnexion);
    }

    public static function getLoginUtilisateurConnecte(): ?string {
        $session = Session::getInstance();
        if ($session->existeCle(ConnexionUtilisateur::$cleConnexion)) {
            return $session->lire(ConnexionUtilisateur::$cleConnexion);
        } else
            return null;
    }

    public static function estUtilisateur($login): bool{
        return (ConnexionUtilisateur::estConnecte() &&
            ConnexionUtilisateur::getLoginUtilisateurConnecte() == $login
        );
    }

//    public static function estAdministrateur() : bool {
//        $loginConnecte = ConnexionUtilisateur::getLoginUtilisateurConnecte();
//
//        // Si personne n'est connecté
//        if ($loginConnecte === null)
//            return false;
//
//        $utilisateurRepository = new UtilisateurRepository();
//        /** @var Utilisateur $utilisateurConnecte */
//        $utilisateurConnecte = $utilisateurRepository->recupererParClePrimaire($loginConnecte);
//
//        return ($utilisateurConnecte !== null);
//    }
}
