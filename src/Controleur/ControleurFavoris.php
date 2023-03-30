<?php

namespace Navigator\Controleur;

use Navigator\Service\FavorisServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class ControleurFavoris extends ControleurGenerique {

    private FavorisServiceInterface $favorisService;

    public function __construct(FavorisServiceInterface $favorisService) {
        $this->favorisService = $favorisService;
    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "favoris");
    }

    public function afficherListe(): Response {
        $favoris = $this->favorisService->recupererFavoris();
        return parent::afficherVue('vueGenerale.php', [
            "favoris" => $favoris,
            "pagetitle" => "Liste des favoris",
            "cheminVueBody" => "favoris/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire(): void {
    }
}