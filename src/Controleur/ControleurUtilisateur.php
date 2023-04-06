<?php

namespace Navigator\Controleur;

use Navigator\Configuration\Configuration;
use Navigator\Lib\ConnexionUtilisateurInterface;
use Navigator\Lib\ConnexionUtilisateurSession;
use Navigator\Lib\MessageFlash;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurUtilisateur extends ControleurGenerique {

    public function __construct(
        private readonly UtilisateurServiceInterface   $utilisateurService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurJWT
    ) {
    }

    public static function afficherErreur($errorMessage = "", $statusCode = ""): Response {
        return parent::afficherErreur($errorMessage, "utilisateur");
    }


    public function afficherListe(): Response {
        $utilisateurs = $this->utilisateurService->recupererUtilisateurs();
        return ControleurUtilisateur::afficherTwig('utilisateur/liste.html.twig', [
            "utilisateurs" => $utilisateurs,
            "pagetitle" => "Liste des utilisateurs"
        ]);
    }

    public function afficherDetail(): Response {
        $utilisateur = null;
        try {
            $login = $this->connexionUtilisateurSession->getLoginUtilisateurConnecte();
            $utilisateur = $this->utilisateurService->afficherDetailUtilisateur($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        $voiture = array(
            "marque" => $utilisateur->getMarqueVehicule(),
            "modele" => $utilisateur->getModeleVehicule()
        );

        return parent::afficherTwig('utilisateur/detail.html.twig', [
            "utilisateur" => $utilisateur,
            "voiture" => $voiture,
            "pagetitle" => "Détail de l'utilisateur",
        ]);
    }

    public function supprimer(): RedirectResponse {
        $login = $_REQUEST['login'];

        try {
            $this->utilisateurService->supprimerUtilisateur($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été supprimé !");
        return ControleurUtilisateur::rediriger("navigator");
    }

    public function afficherFormulaireCreation(): Response {
        return ControleurUtilisateur::afficherTwig('utilisateur/formulaireCreation.html.twig', [
            "pagetitle" => "Création d'un utilisateur"
        ]);
    }

    public function creerDepuisFormulaire(): RedirectResponse {
        $login = $_REQUEST['login'];
        $nom = $_REQUEST['nom'];
        $prenom = $_REQUEST['prenom'];
        $motDePasse = $_REQUEST['mdp'];
        $motDePasse2 = $_REQUEST['mdp2'];
        $marqueVehicule = $_REQUEST['marqueVehicule'];
        $modeleVehicule = $_REQUEST['modeleVehicule'];
        try {
            $this->utilisateurService->creerUtilisateur($login, $nom, $prenom, $motDePasse, $motDePasse2, $marqueVehicule, $modeleVehicule);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        return ControleurUtilisateur::rediriger("map");
    }

    public function afficherFormulaireMiseAJour(): Response {
        $login = $_REQUEST['login'];
        $utilisateur = null;

        try {
            $utilisateur = $this->utilisateurService->afficherFormulaireMAJUtilisateur($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        $loginHTML = htmlspecialchars($login);
        $prenomHTML = htmlspecialchars($utilisateur->getPrenom());
        $nomHTML = htmlspecialchars($utilisateur->getNom());
        $emailHTML = htmlspecialchars($utilisateur->getEmail());
        return ControleurUtilisateur::afficherTwig('utilisateur/formulaireMiseAJour.html.twig', [
            "pagetitle" => "Mise à jour d'un utilisateur",
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
        $marqueVehicule = $_REQUEST['marqueVehicule'];
        $modeleVehicule = $_REQUEST['modeleVehicule'];

        try {
            $this->utilisateurService->mettreAJourUtilisateur($login, $nom, $prenom, $motDePasseAncien, $motDePasse, $motDePasse2, $marqueVehicule, $modeleVehicule);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
        return ControleurUtilisateur::rediriger("pagePerso");
    }

    public function getVoiture() : Response {
        try {
            $login = $this->connexionUtilisateurSession->getLoginUtilisateurConnecte();
            $utilisateur = $this->utilisateurService->afficherDetailUtilisateur($login);
            return new JsonResponse([
                "marque" => $utilisateur->getMarqueVehicule(),
                "modele" => $utilisateur->getModeleVehicule()
            ], Response::HTTP_OK);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return new JsonResponse("Erreur", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateVoiture() : Response {
        $marque = $_REQUEST['marque'];
        $modele = $_REQUEST['modele'];

        try {
            $login = $this->connexionUtilisateurSession->getLoginUtilisateurConnecte();
            $this->utilisateurService->updateVoiture($login, $marque, $modele);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        MessageFlash::ajouter("success", "La voiture a bien été modifiée !");
        return ControleurUtilisateur::rediriger("pagePerso");
    }

    public function afficherFormulaireConnexion(): Response {
        return ControleurUtilisateur::afficherTwig('utilisateur/formulaireConnexion.html.twig', [
            "pagetitle" => "Formulaire de connexion",
            "method" => Configuration::getDebug() ? "get" : "post",
        ]);
    }

    public function connecter(): RedirectResponse {
        $login = $_REQUEST['login'];
        $motDePasse = $_REQUEST['mdp'];

        try {
            $login = $this->utilisateurService->verifierIdentifiantUtilisateur($login, $motDePasse);
            $this->connexionUtilisateurJWT->connecter($login);
            $this->connexionUtilisateurSession->connecter($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }

        MessageFlash::ajouter("success", "Connexion effectuée.");
        return ControleurUtilisateur::rediriger("map");
    }

    public function deconnecter(): RedirectResponse {
        if (!$this->connexionUtilisateurSession->estConnecte()) {
            MessageFlash::ajouter("error", "Utilisateur non connecté.");
            return ControleurGenerique::rediriger('navigator');
        }
        $this->connexionUtilisateurSession->deconnecter();
        $this->connexionUtilisateurJWT->deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return ControleurUtilisateur::rediriger("navigator");
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
