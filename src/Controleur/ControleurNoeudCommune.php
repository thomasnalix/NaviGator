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

    public static function getNoeudProche():void {
        $noeudCommuneRepository = new NoeudCommuneRepository();
        $information = $noeudCommuneRepository->getNoeudProche($_GET['lat'], $_GET['long']);

        echo json_encode($information);
    }


    public static function plusCourtChemin(): void {
        $parameters = [
            "pagetitle" => "Plus court chemin",
            "cheminVueBody" => "noeudCommune/plusCourtChemin.php",
        ];

        if (!empty($_POST)) {




            $noeudCommuneRepository = new NoeudCommuneRepository();
            $noeudRoutierRepository = new NoeudRoutierRepository();

            $communes = [];
            $noeudRoutier = [];
            for($i = 0; $i < $_POST['nbField']; $i++) {
                if ($_POST["gid" . $i] != "") {
                    $noeudRoutier[] = $noeudRoutierRepository->recupererParGid($_POST["gid" . $i]);
                    $communes[] = $_POST["gid" . $i];
                } else {
                    $noeudCommune = $noeudCommuneRepository->recupererPar(["nom_comm" => $_POST["commune" . $i]])[0];
                    $noeudRoutier[] = $noeudRoutierRepository->recupererNoeudRoutier($noeudCommune->getId_nd_rte());
                    $communes[] = $_POST["commune" . $i];
                }
            }

            $pcc = new PlusCourtChemin($noeudRoutier);

            $now = microtime(true);
            $distance = $pcc->aStarDistance();
            echo "Temps d'A* : " . (microtime(true) - $now) . "s<br>";
            $parameters["distance"] = $distance[0];

            $now = microtime(true);
            $parameters["chemin"] = $noeudRoutierRepository->calculerItineraire($distance[1]);
            echo "Temps chemin : " . (microtime(true) - $now) . "s<br>";

            $parameters["temps"] = $distance[2];

            $parameters["nomCommuneDepart"] = $communes[0];
            $parameters["nomCommuneArrivee"] = $communes[count($communes) - 1];

        }
        ControleurNoeudCommune::afficherVue('vueGenerale.php', $parameters);
    }


}
