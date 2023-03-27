<?php

namespace Navigator\Controleur;

use Navigator\Configuration\Configuration;
use Navigator\Lib\ConnexionUtilisateur;
use Navigator\Lib\MessageFlash;
use Navigator\Lib\MotDePasse;
use Navigator\Modele\DataObject\Utilisateur;
use Navigator\Modele\Repository\UtilisateurRepository;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\UtilisateurService;

class ControleurUtilisateur extends ControleurGenerique {

    public static function afficherErreur($errorMessage = "", $controleur = ""): void {
        parent::afficherErreur($errorMessage, "utilisateur");
    }


    public static function afficherListe(): void
    {
        $utilisateurs = (new UtilisateurService())->recupererUtilisateurs();
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "utilisateurs" => $utilisateurs,
            "pagetitle" => "Liste des utilisateurs",
            "cheminVueBody" => "utilisateur/liste.php"
        ]);
    }

    public static function afficherDetail(): void {
        $login = $_REQUEST['login'];
        $utilisateur = null;

        try {
            $utilisateur = (new UtilisateurService())->afficherDetailUtilisateur($login);
        } catch(ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
        }

        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "utilisateur" => $utilisateur,
            "pagetitle" => "Détail de l'utilisateur",
            "cheminVueBody" => "utilisateur/detail.php"
        ]);
    }

    public static function supprimer() {
        $login = $_REQUEST['login'];

        try {
            (new UtilisateurService())->supprimerUtilisateur($login);
        } catch(ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été supprimé !");
        ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

    public static function afficherFormulaireCreation(): void {
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'un utilisateur",
            "cheminVueBody" => "utilisateur/formulaireCreation.php",
            "method" => Configuration::getDebug() ? "get" : "post",
        ]);
    }

    public static function creerDepuisFormulaire(): void {
        $login = $_REQUEST['login'];
        $nom = $_REQUEST['nom'];
        $prenom = $_REQUEST['prenom'];
        $motDePasse = $_REQUEST['mdp'];
        $motDePasse2 = $_REQUEST['mdp2'];
        $email = $_REQUEST['email'];
        $imageProfil = $_REQUEST['imageProfil'];

        try {
            (new UtilisateurService())->creerUtilisateur($login, $nom, $prenom, $motDePasse, $motDePasse2, $email, $imageProfil);
        } catch(ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurUtilisateur::rediriger("utilisateur", "afficherFormulaireCreation");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

    public static function afficherFormulaireMiseAJour(): void {
            $login = $_REQUEST['login'];
            $utilisateur = null;

            try {
                $utilisateur = (new UtilisateurService())->afficherFormulaireMAJUtilisateur($login);
            } catch(ServiceException $e) {
                MessageFlash::ajouter("danger", $e->getMessage());
                ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
            }

            $loginHTML = htmlspecialchars($login);
            $prenomHTML = htmlspecialchars($utilisateur->getPrenom());
            $nomHTML = htmlspecialchars($utilisateur->getNom());
            $emailHTML = htmlspecialchars($utilisateur->getEmail());
            ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Mise à jour d'un utilisateur",
                "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
                "loginHTML" => $loginHTML,
                "prenomHTML" => $prenomHTML,
                "nomHTML" => $nomHTML,
                "emailHTML" => $emailHTML,
                "method" => Configuration::getDebug() ? "get" : "post",
            ]);
    }

    public static function mettreAJour(): void {
        $login = $_REQUEST['login'];
        $nom = $_REQUEST['nom'];
        $prenom = $_REQUEST['prenom'];
        $motDePasseAncien = $_REQUEST['mdpAncien'];
        $motDePasse = $_REQUEST['mdp'];
        $motDePasse2 = $_REQUEST['mdp2'];
        $email = $_REQUEST['email'];
        $imageProfil = $_REQUEST['imageProfil'];

        try {
            (new UtilisateurService())->mettreAJourUtilisateur($login, $nom, $prenom, $motDePasseAncien, $motDePasse, $motDePasse2, $email, $imageProfil);
        } catch(ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurUtilisateur::rediriger("utilisateur", "afficherFormulaireMiseAJour", ["login" => $login]);
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
        ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

    public static function afficherFormulaireConnexion(): void {
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Formulaire de connexion",
            "cheminVueBody" => "utilisateur/formulaireConnexion.php",
            "method" => Configuration::getDebug() ? "get" : "post",
        ]);
    }

    public static function connecter(): void {
        $login = $_REQUEST['login'];
        $motDePasse = $_REQUEST['mdp'];

        try {
            (new UtilisateurService())->connecterUtilisateur($login, $motDePasse);
        } catch(ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurUtilisateur::rediriger("utilisateur", "afficherFormulaireConnexion");
        }

        MessageFlash::ajouter("success", "Connexion effectuée.");
        ControleurUtilisateur::rediriger("utilisateur", "afficherDetail", ["login" => $_REQUEST["login"]]);
    }

    public static function deconnecter(): void {
        (new UtilisateurService())->deconnecterUtilisateur();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

//    public static function validerEmail() {
//        if (isset($_REQUEST['login']) && isset($_REQUEST['nonce'])) {
//            $succesValidation = VerificationEmail::traiterEmailValidation($_REQUEST["login"], $_REQUEST["nonce"]);
//
//            if (!$succesValidation) {
//                MessageFlash::ajouter("warning", "Email de validation incorrect.");
//                ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
//            }
//
//            $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire($_REQUEST["login"]);
//            MessageFlash::ajouter("warning", "Validation d'email réussie");
//            ControleurUtilisateur::rediriger("utilisateur", "afficherDetail", ["login" => $_REQUEST["login"]]);
//        } else {
//            MessageFlash::ajouter("danger", "Login ou nonce manquant.");
//            ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
//        }
//    }
}
