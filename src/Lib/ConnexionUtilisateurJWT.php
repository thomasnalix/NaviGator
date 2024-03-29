<?php

namespace Navigator\Lib;

use Navigator\Modele\HTTP\Cookie;

class ConnexionUtilisateurJWT implements ConnexionUtilisateurInterface {

    public function connecter(string $idUtilisateur): void {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["idUtilisateur" => $idUtilisateur]));
    }

    public function estConnecte(?string $login = null): bool {
        return !is_null($this->getLoginUtilisateurConnecte()) && ($login === null || $login === $this->getLoginUtilisateurConnecte());
    }

    public function deconnecter(): void {
        if (Cookie::existeCle("auth_token"))
            Cookie::supprimer("auth_token");
    }

    public function getLoginUtilisateurConnecte(): ?string {
        if (Cookie::existeCle("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnees = JsonWebToken::decoder($jwt);
            return $donnees["idUtilisateur"] ?? null;
        } else
            return null;
    }

}