<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Lib\MessageFlash;
use App\PlusCourtChemin\Lib\PlusCourtChemin;
use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class ControleurNoeudCommune extends ControleurGenerique {

    public static function afficherErreur($errorMessage = "", $controleur = ""): void {
        parent::afficherErreur($errorMessage, "noeudCommune");
    }

    public static function afficherListe(): void {
        $noeudsCommunes = (new NoeudCommuneRepository())->recuperer();     //appel au modèle pour gerer la BD
        ControleurNoeudCommune::afficherVue('vueGenerale.php', [
            "noeudsCommunes" => $noeudsCommunes,
            "pagetitle" => "Liste des Noeuds Routiers",
            "cheminVueBody" => "noeudCommune/liste.php"
        ]);
    }

    public static function afficherDetail(): void {
        if (!isset($_REQUEST['gid'])) {
            MessageFlash::ajouter("danger", "Immatriculation manquante.");
            ControleurNoeudCommune::rediriger("noeudCommune", "afficherListe");
        }

        $gid = $_REQUEST['gid'];
        $noeudCommune = (new NoeudCommuneRepository())->recupererParClePrimaire($gid);

        if ($noeudCommune === null) {
            MessageFlash::ajouter("warning", "gid inconnue.");
            ControleurNoeudCommune::rediriger("noeudCommune", "afficherListe");
        }

        ControleurNoeudCommune::afficherVue('vueGenerale.php', [
            "noeudCommune" => $noeudCommune,
            "pagetitle" => "Détail de la noeudCommune",
            "cheminVueBody" => "noeudCommune/detail.php"
        ]);
    }


    public static function plusCourtChemin(): void {
        $parameters = [
            "pagetitle" => "Plus court chemin",
            "cheminVueBody" => "noeudCommune/plusCourtChemin.php",
        ];

        if (!empty($_POST)) {

            $communes = [];
            for($i = 0; $i < $_POST['nbField']; $i++)
                $communes[] = $_POST["commune" . $i];

            $noeudCommuneRepository = new NoeudCommuneRepository();

            $noeudCommunes = [];
            foreach ($communes as $commune)
                $noeudCommunes[] = $noeudCommuneRepository->recupererPar(["nom_comm" => $commune])[0];

            $noeudRoutierRepository = new NoeudRoutierRepository();
            $noeudRoutier = [];
            foreach ($noeudCommunes as $noeudCommune)
                $noeudRoutier[] = $noeudRoutierRepository->recupererNoeudRoutier($noeudCommune->getId_nd_rte());

            $pcc = new PlusCourtChemin($noeudRoutier);


            $distance = $pcc->aStarDistance();
            $parameters["distance"] = $distance[0];
            $parameters["chemin"] = $distance[1];
            $parameters["temps"] = $distance[2];

            $parameters["nomCommuneDepart"] = $communes[0];
            $parameters["nomCommuneArrivee"] = $communes[count($communes) - 1];

        }
        ControleurNoeudCommune::afficherVue('vueGenerale.php', $parameters);
    }


}
