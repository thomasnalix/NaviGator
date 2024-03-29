<?php

namespace Navigator\Service;

use Navigator\Lib\ConnexionUtilisateurInterface;
use Navigator\Lib\MotDePasse;
use Navigator\Modele\DataObject\Utilisateur;
use Navigator\Modele\Repository\UtilisateurRepositoryInterface;
use Navigator\Service\Exception\ServiceException;

class UtilisateurService implements UtilisateurServiceInterface {

    private UtilisateurRepositoryInterface $utilisateurRepository;

    private ConnexionUtilisateurInterface $connexionUtilisateur;

    public function __construct(ConnexionUtilisateurInterface $connexionUtilisateur, UtilisateurRepositoryInterface $utilisateurRepository) {
        $this->connexionUtilisateur = $connexionUtilisateur;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function creerUtilisateur($login, $nom, $prenom, $motDePasse, $motDePasse2, $marqueVehicule, $modeleVehicule) {
        if ($login == null || $motDePasse == null) throw new ServiceException("Les champs login et mot de passe sont obligatoires");
        if ($motDePasse != $motDePasse2) throw new ServiceException("Les mots de passe ne correspondent pas");

        $utilisateur = $this->utilisateurRepository->creer($login, $nom, $prenom, $motDePasse, $marqueVehicule, $modeleVehicule);
        $succesSauvegarde = $this->utilisateurRepository->ajouter($utilisateur);

        if (!$succesSauvegarde) throw new ServiceException("Erreur lors de la création de l'utilisateur");
    }

    public function supprimerUtilisateur($login) {
        if ($login == null) throw new ServiceException("Login manquant", 400);
        $succesSuppression = $this->utilisateurRepository->supprimer($login);
        if (!$succesSuppression) throw new ServiceException("Login inconnu", 404);
        $this->connexionUtilisateur->deconnecter();
    }

    public function mettreAJourUtilisateur($login, $nom, $prenom, $motDePasseAncien, $motDePasse, $motDePasse2, $marqueVehicule, $modeleVehicule) {
        if ($login == null || $motDePasse == null) throw new ServiceException("Les champs login et mot de passe sont manquants", 400);
        if ($motDePasse != $motDePasse2) throw new ServiceException("Les mots de passe ne correspondent pas", 400);
        if (!$this->connexionUtilisateur->estConnecte()) throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté", 403);

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur == null) throw new ServiceException("Login inconnu", 404);
        if (!MotDePasse::verifier($motDePasseAncien, $utilisateur->getMotDePasse())) throw new ServiceException("Ancien mot de passe erroné", 403);
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setMotDePasse($motDePasse);
        $utilisateur->setMarqueVehicule($marqueVehicule);
        $utilisateur->setModeleVehicule($modeleVehicule);

        if (!$this->utilisateurRepository->mettreAJour($utilisateur))
            throw new ServiceException("Erreur lors de la mise à jour de l'utilisateur", 500);
    }

    public function verifierIdentifiantUtilisateur($login, $motDePasse): string {
        if ($login == null || $motDePasse == null) throw new ServiceException("Login ou mot de passe manquant", 400);

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur == null) throw new ServiceException("Login inconnu", 404);
        if (!(MotDePasse::verifier($motDePasse, $utilisateur->getMotDePasse()))) throw new ServiceException("Mot de passe erroné " . $utilisateur->getMotDePasse() . " " . $motDePasse, 403);

        return $utilisateur->getLogin();
    }

    public function recupererUtilisateurs(): array {
        return $this->utilisateurRepository->recuperer();
    }

    public function recupererUtilisateurParId($id): Utilisateur {
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($id);
        if ($utilisateur === null) throw new ServiceException("Utilisateur inconnu", 404);
        return $utilisateur;
    }

    public function afficherDetailUtilisateur($login): Utilisateur {
        if ($login == null) throw new ServiceException("Login manquant", 400);
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur === null) throw new ServiceException("L'utilisateur n'existe pas", 404);
        return $utilisateur;
    }

    public function afficherFormulaireMAJUtilisateur($login): Utilisateur {
        if (!$this->connexionUtilisateur->estConnecte($login)) throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté");
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur === null) throw new ServiceException("L'utilisateur n'existe pas", 404);
        return $utilisateur;
    }

    public function updateVoiture($login, $marque, $modele): bool {
        if (!$this->connexionUtilisateur->estConnecte($login)) throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté");
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur === null) throw new ServiceException("L'utilisateur n'existe pas", 404);
        $utilisateur->setMarqueVehicule($marque);
        $utilisateur->setModeleVehicule($modele);
        return $this->utilisateurRepository->mettreAJour($utilisateur);
    }

}