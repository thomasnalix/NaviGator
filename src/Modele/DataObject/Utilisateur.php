<?php

namespace Navigator\Modele\DataObject;

use Navigator\Lib\MotDePasse;

class Utilisateur extends AbstractDataObject implements \JsonSerializable {


    public function __construct(
        private string $login,
        private string $nom,
        private string $prenom,
        private string $motDePasse,
        private string $email,
        private string $imageProfil) {
    }


    public static function construireDepuisFormulaire(array $tableauFormulaire): Utilisateur {
        return new Utilisateur(
            $tableauFormulaire["login"],
            $tableauFormulaire["nom"],
            $tableauFormulaire["prenom"],
            MotDePasse::hacher($tableauFormulaire["mdp"]),
            $tableauFormulaire["email"],
            $tableauFormulaire["imageProfil"]
        );
    }

    public function jsonSerialize(): array {
        return [
            "login" => $this->login,
            "nom" => $this->nom,
            "prenom" => $this->prenom,
            "email" => $this->email,
            "imageProfil" => $this->imageProfil
        ];
    }

    public function getLogin(): string {
        return $this->login;
    }

    public function setLogin(string $login): void {
        $this->login = $login;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function getMotDePasse(): string {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): void {
        $this->motDePasse = $motDePasse;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getImageProfil(): string {
        return $this->imageProfil;
    }

    public function setImageProfil(string $imageProfil): void {
        $this->imageProfil = $imageProfil;
    }

    public function exporterEnFormatRequetePreparee(): array {
        return array(
            "login_tag" => $this->login,
            "nom_tag" => $this->nom,
            "prenom_tag" => $this->prenom,
            "motDePasse_tag" => $this->motDePasse,
            "email_tag" => $this->email,
            "imageProfil_tag" => $this->imageProfil
        );
    }
}
