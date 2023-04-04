<?php

namespace Navigator\Lib;

interface ConnexionUtilisateurInterface {
    public function connecter(string $loginUtilisateur): void;

    public function estConnecte(?string $login = null): bool;

    public function deconnecter() : void;

    public function getLoginUtilisateurConnecte() : ?string;

}