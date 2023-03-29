<?php

namespace Navigator\Modele\Repository;

use Navigator\Modele\DataObject\Utilisateur;

interface UtilisateurRepositoryInterface
{
    /**
     * @param string $login
     * @param string $nom
     * @param string $prenom
     * @param string $motDePasse
     * @param string $email
     * @param string $imageProfil
     * @return Utilisateur
     */
    public function creer(string $login, string $nom, string $prenom, string $motDePasse, string $email, string $imageProfil): Utilisateur;

    /**
     * @param Utilisateur $utilisateur
     * @return bool
     */
    public function ajouter(Utilisateur $utilisateur) : bool;

    /**
     * @param string $login
     * @return bool
     */
    public function supprimer(string $login) : bool;

    /**
     * @param Utilisateur $utilisateur
     * @return bool
     */
    public function mettreAJour(Utilisateur $utilisateur) : bool;

}