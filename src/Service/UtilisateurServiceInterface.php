<?php

namespace Navigator\Service;

use Navigator\Modele\DataObject\Utilisateur;

interface UtilisateurServiceInterface {
    public function creerUtilisateur($login, $nom, $prenom, $motDePasse, $motDePasse2, $marqueVehicule, $modeleVehicule);

    public function supprimerUtilisateur($login);

    public function mettreAJourUtilisateur($login, $nom, $prenom, $motDePasseAncien, $motDePasse, $motDePasse2, $marqueVehicule, $modeleVehicule);

    public function verifierIdentifiantUtilisateur($login, $motDePasse): string;

    public function recupererUtilisateurs(): array;

    public function recupererUtilisateurParId($id): Utilisateur;

    public function afficherDetailUtilisateur($login): Utilisateur;

    public function afficherFormulaireMAJUtilisateur($login): Utilisateur;

    public function updateVoiture($login, $marque, $modele) : bool;

}