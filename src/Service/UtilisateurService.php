<?php

namespace Navigator\Service;

use Navigator\Controleur\ControleurUtilisateur;
use Navigator\Lib\ConnexionUtilisateur;
use Navigator\Lib\MotDePasse;
use Navigator\Modele\DataObject\AbstractDataObject;
use Navigator\Modele\DataObject\Utilisateur;
use Navigator\Modele\Repository\UtilisateurRepository;
use Navigator\Modele\Repository\UtilisateurRepositoryInterface;
use Navigator\Service\Exception\ServiceException;

class UtilisateurService {

    public static function creerUtilisateur($login, $nom, $prenom, $motDePasse, $motDePasse2, $email, $imageProfil) {
        if ($login == null || $motDePasse == null || $email == null || $imageProfil == null) throw new ServiceException("Les champs login, mot de passe et email sont obligatoires");
        if ($motDePasse != $motDePasse2) throw new ServiceException("Les mots de passe ne correspondent pas");
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ServiceException("L'adresse email n'est pas valide");

        $utilisateurRepository = new UtilisateurRepository();
        $utilisateur = $utilisateurRepository->creer($login, $nom, $prenom, $motDePasse, $email, $imageProfil);
        $succesSauvegarde = $utilisateurRepository->ajouter($utilisateur);

        if (!$succesSauvegarde) throw new ServiceException("Erreur lors de la création de l'utilisateur");
    }

    public static function supprimerUtilisateur($login) {
        if ($login == null) throw new ServiceException("Login manquant", 400);

        $succesSuppression = (new UtilisateurRepository())->supprimer($login);
        if (!$succesSuppression) throw new ServiceException("Login inconnu", 404);
        self::deconnecterUtilisateur();
    }

    public static function mettreAJourUtilisateur($login, $nom, $prenom, $motDePasseAncien, $motDePasse, $motDePasse2, $email, $imageProfil) {
        if ($login == null || $motDePasse == null || $email == null) throw new ServiceException("Les champs login, mot de passe et email sont manquants", 400);
        if ($motDePasse != $motDePasse2) throw new ServiceException("Les mots de passe ne correspondent pas", 400);
        if (!(ConnexionUtilisateur::estConnecte())) throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté", 403);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ServiceException("L'adresse email n'est pas valide", 400);
        if ($imageProfil == null || strlen($imageProfil) == 0) throw new ServiceException("L'image de profil est manquante", 400);

        $utilisateurRepository = new UtilisateurRepository();
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur == null) throw new ServiceException("Login inconnu", 404);
        if (!MotDePasse::verifier($motDePasseAncien, $utilisateur->getMotDePasse())) throw new ServiceException("Ancien mot de passe erroné", 403);
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setMotDePasse($motDePasse);
        $utilisateur->setEmail($email);
        $utilisateur->setImageProfil($imageProfil);

        if (!$utilisateurRepository->mettreAJour($utilisateur))
            throw new ServiceException("Erreur lors de la mise à jour de l'utilisateur", 500);
    }

    public static function connecterUtilisateur($login, $motDePasse) {
        if ($login == null || $motDePasse == null) throw new ServiceException("Login ou mot de passe manquant", 400);

        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire($login);
        if ($utilisateur == null) throw new ServiceException("Login inconnu", 404);
        if (!(MotDePasse::verifier($motDePasse, $utilisateur->getMotDePasse()))) throw new ServiceException("Mot de passe erroné " . $utilisateur->getMotDePasse() . " " . $motDePasse, 403);

        ConnexionUtilisateur::connecter($login);
    }

    public static function deconnecterUtilisateur() {
        if (!ConnexionUtilisateur::estConnecte()) throw new ServiceException("Vous n'êtes pas connecté", 401);
        ConnexionUtilisateur::deconnecter();
    }

    public static function recupererUtilisateurs(): array {
        return (new UtilisateurRepository())->recuperer();
    }

    public static function afficherDetailUtilisateur($login): AbstractDataObject {
        if ($login == null) throw new ServiceException("Login manquant");
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire($login);
        if ($utilisateur === null) throw new ServiceException("L'utilisateur n'existe pas", 404);
        return $utilisateur;
    }

    public static function afficherFormulaireMAJUtilisateur($login): AbstractDataObject {
        if ($login == null) throw new ServiceException("Login manquant");
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire($login);
        if ($utilisateur === null) throw new ServiceException("L'utilisateur n'existe pas", 404);
        if (!(ConnexionUtilisateur::estConnecte($login))) throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté");
        return $utilisateur;
    }

}