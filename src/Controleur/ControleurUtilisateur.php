<?php

namespace Navigator\Controleur;

use Navigator\Configuration\Configuration;
use Navigator\Lib\ConnexionUtilisateurInterface;
use Navigator\Lib\ConnexionUtilisateurSession;
use Navigator\Lib\MessageFlash;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurUtilisateur extends ControleurGenerique {

    public function __construct(
        private readonly UtilisateurServiceInterface   $utilisateurService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurJWT
    ) {
    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "utilisateur");
    }


    public function afficherListe(): Response {
        $utilisateurs = $this->utilisateurService->recupererUtilisateurs();
        return ControleurUtilisateur::afficherVue('base.html.twig', [
            "utilisateurs" => $utilisateurs,
            "pagetitle" => "Liste des utilisateurs",
            "cheminVueBody" => "utilisateur/liste.php"
        ]);
    }

    public function afficherDetail(): Response {
        $login = $_REQUEST['login'];
        $utilisateur = null;
        try {
            $utilisateur = $this->utilisateurService->afficherDetailUtilisateur($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
        }

        return parent::afficherVue('base.html.twig', [
            "utilisateur" => $utilisateur,
            "pagetitle" => "Détail de l'utilisateur",
            "cheminVueBody" => "utilisateur/detail.php"
        ]);
    }

    public function supprimer(): RedirectResponse {
        $login = $_REQUEST['login'];

        try {
            $this->utilisateurService->supprimerUtilisateur($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été supprimé !");
        return ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

    public function afficherFormulaireCreation(): Response {
        return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'un utilisateur",
            "cheminVueBody" => "utilisateur/formulaireCreation.php",
            "method" => Configuration::getDebug() ? "get" : "post",
        ]);
    }

    public function creerDepuisFormulaire(): RedirectResponse {
        $login = $_REQUEST['login'];
        $nom = $_REQUEST['nom'];
        $prenom = $_REQUEST['prenom'];
        $motDePasse = $_REQUEST['mdp'];
        $motDePasse2 = $_REQUEST['mdp2'];
        $email = $_REQUEST['email'];
        $imageProfil = $_REQUEST['imageProfil'];

        try {
            $this->utilisateurService->creerUtilisateur($login, $nom, $prenom, $motDePasse, $motDePasse2, $email, $imageProfil);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("utilisateur", "afficherFormulaireCreation");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        return ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

    public function afficherFormulaireMiseAJour(): Response {
        $login = $_REQUEST['login'];
        $utilisateur = null;

        try {
            $utilisateur = $this->utilisateurService->afficherFormulaireMAJUtilisateur($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
        }

        $loginHTML = htmlspecialchars($login);
        $prenomHTML = htmlspecialchars($utilisateur->getPrenom());
        $nomHTML = htmlspecialchars($utilisateur->getNom());
        $emailHTML = htmlspecialchars($utilisateur->getEmail());
        return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Mise à jour d'un utilisateur",
            "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
            "loginHTML" => $loginHTML,
            "prenomHTML" => $prenomHTML,
            "nomHTML" => $nomHTML,
            "emailHTML" => $emailHTML,
            "method" => Configuration::getDebug() ? "get" : "post",
        ]);
    }

    public function mettreAJour(): RedirectResponse {
        $login = $_REQUEST['login'];
        $nom = $_REQUEST['nom'];
        $prenom = $_REQUEST['prenom'];
        $motDePasseAncien = $_REQUEST['mdpAncien'];
        $motDePasse = $_REQUEST['mdp'];
        $motDePasse2 = $_REQUEST['mdp2'];
        $email = $_REQUEST['email'];
        $imageProfil = $_REQUEST['imageProfil'];

        try {
            $this->utilisateurService->mettreAJourUtilisateur($login, $nom, $prenom, $motDePasseAncien, $motDePasse, $motDePasse2, $email, $imageProfil);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("utilisateur", "afficherFormulaireMiseAJour", ["login" => $login]);
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
        return ControleurUtilisateur::rediriger("utilisateur", "afficherListe");
    }

    public function afficherFormulaireConnexion(): Response {
        return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Formulaire de connexion",
            "cheminVueBody" => "utilisateur/formulaireConnexion.php",
            "method" => Configuration::getDebug() ? "get" : "post",
        ]);
    }

    public function connecter(): RedirectResponse {
        $login = $_REQUEST['login'];
        $motDePasse = $_REQUEST['mdp'];

        try {
            $login = $this->utilisateurService->verifierIdentifiantUtilisateur($login, $motDePasse);
            $this->connexionUtilisateurSession->connecter($login);
            $this->connexionUtilisateurJWT->connecter($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("utilisateur", ["afficherFormulaireConnexion"]);
        }

        MessageFlash::ajouter("success", "Connexion effectuée.");
        return ControleurUtilisateur::rediriger("utilisateur", "afficherDetail", ["login" => $_REQUEST["login"]]);
    }

    public function deconnecter(): RedirectResponse {
        if (!$this->connexionUtilisateurSession->estConnecte()) {
            MessageFlash::ajouter("error", "Utilisateur non connecté.");
            return ControleurGenerique::rediriger('connecter');
        }
        $this->connexionUtilisateurSession->deconnecter();
        $this->connexionUtilisateurJWT->deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return ControleurUtilisateur::rediriger("utilisateur", ["afficherListe"]);
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
