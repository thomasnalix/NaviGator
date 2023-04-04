<?php

namespace Navigator\Controleur;

use Symfony\Component\HttpFoundation\Response;

class ControleurNoeudCommune extends ControleurGenerique {


    public function __construct() {

    }

    public static function afficherErreur($errorMessage = "", $statusCode = ""): Response {
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