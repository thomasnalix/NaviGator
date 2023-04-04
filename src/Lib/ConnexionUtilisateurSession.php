<?php

namespace Navigator\Lib;

use Navigator\Modele\HTTP\Session;

class ConnexionUtilisateurSession implements ConnexionUtilisateurInterface {
    private static string $cleConnexion = "_utilisateurConnecte";

    public function connecter(string $loginUtilisateur): void {
        $session = Session::getInstance();
        $session->enregistrer(ConnexionUtilisateurSession::$cleConnexion, $loginUtilisateur);
    }

    public function estConnecte(?string $login = null): bool {
        $session = Session::getInstance();
        return $session->existeCle(ConnexionUtilisateurSession::$cleConnexion) &&
            ($login === null || $login === $session->lire(ConnexionUtilisateurSession::$cleConnexion));
    }

    public function deconnecter(): void {
        $session = Session::getInstance();
        $session->supprimer(ConnexionUtilisateurSession::$cleConnexion);
    }

    public function getLoginUtilisateurConnecte(): ?string {
        $session = Session::getInstance();
        if ($session->existeCle(ConnexionUtilisateurSession::$cleConnexion)) {
            return $session->lire(ConnexionUtilisateurSession::$cleConnexion);
        } else
            return null;
    }

//    public function estUtilisateur($login): bool{
//        return ($this->estConnecte() &&
//            $this->getLoginUtilisateurConnecte() == $login
//        );
//    }

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
