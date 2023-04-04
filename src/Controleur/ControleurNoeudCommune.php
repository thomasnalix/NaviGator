<?php

namespace Navigator\Controleur;

use Navigator\Lib\MessageFlash;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudCommuneServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurNoeudCommune extends ControleurGenerique {



    public function __construct() {

    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "noeudCommune");
    }


    /**
     * Load the page to find the shortest path between cities
     * @return Response
     */
    public static function plusCourtChemin(): Response {
        $parameters = [
            "pagetitle" => "Plus court chemin",
        ];
        return ControleurNoeudCommune::afficherTwig('noeudCommune/plusCourtChemin.html.twig', $parameters);
    }
}