<?php

namespace Navigator\Controleur;

use Navigator\Lib\MessageFlash;
use Navigator\Lib\PlusCourtChemin;
use Navigator\Modele\Repository\NoeudCommuneRepository;
use Navigator\Modele\Repository\NoeudRoutierRepository;

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
            MessageFlash::ajouter("danger", "gid manquant.");
            //ControleurNoeudCommune::rediriger("noeudCommune", "afficherListe");
            ControleurNoeudCommune::rediriger("map");
        }

        $gid = $_REQUEST['gid'];
        $noeudCommune = (new NoeudCommuneRepository())->recupererParClePrimaire($gid);

        if ($noeudCommune === null) {
            MessageFlash::ajouter("warning", "gid inconnue.");;
            //ControleurNoeudCommune::rediriger("noeudCommune", "afficherListe");
            ControleurNoeudCommune::rediriger("map");
        }

        ControleurNoeudCommune::afficherVue('vueGenerale.php', [
            "noeudCommune" => $noeudCommune,
            "pagetitle" => "Détail de la noeudCommune",
            "cheminVueBody" => "noeudCommune/detail.php"
        ]);
    }

    public static function getNoeudProche($long, $lat):void {
        $noeudCommuneRepository = new NoeudCommuneRepository();
        $information = $noeudCommuneRepository->getNoeudProche($lat, $long);
        echo json_encode($information);
    }

    public static function recupererListeCommunes($text): void {
        $noeudsCommunes = (new NoeudRoutierRepository())->getNomCommunes($text);
        // trie par ordre alphabétique
        usort($noeudsCommunes, function($a, $b) use ($text) {
            if (str_starts_with($a, $text) && str_starts_with($b, $text))
                return 0;
            if (str_starts_with($a, $text))
                return -1;
            if (str_starts_with($b, $text))
                return 1;
            return 0;
        });
        echo json_encode($noeudsCommunes);
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
            for ($i = 0; $i < $_POST['nbField']; $i++) {
                if ($_POST["gid" . $i] != "") {
                    $noeudRoutier[] = $noeudRoutierRepository->recupererParGid($_POST["gid" . $i]);
                    $communes[] = $_POST["gid" . $i];
                } else {
                    if (preg_match('/\((\d{5})\)/', $_POST["commune" . $i]))
                        $nomCommune = substr($_POST["commune" . $i], 0, strlen($_POST["commune" . $i]) - 8);
                    else
                        $nomCommune = $_POST["commune" . $i];

                    $noeudCommune = $noeudCommuneRepository->recupererPar(["nom_comm" => $nomCommune])[0];
                    $noeudRoutier[] = $noeudRoutierRepository->recupererNoeudRoutier($noeudCommune->getId_nd_rte());
                    $communes[] = $_POST["commune" . $i];
                }
            }

            $pcc = new PlusCourtChemin($noeudRoutier);

            $now = microtime(true);
            $distance = $pcc->aStarDistance();
            echo "Temps d'A* : " . (microtime(true) - $now) . "s<br>";
            $parameters["distance"] = $distance[0];

            if ($distance[1] != -1)
                $parameters["chemin"] = $noeudRoutierRepository->calculerItineraire($distance[1]);

            $parameters["temps"] = $distance[2];
            $parameters["communes"] = $communes;
            $parameters["nomCommuneDepart"] = $communes[0];
            $parameters["nomCommuneArrivee"] = $communes[count($communes) - 1];

        }
        ControleurNoeudCommune::afficherVue('vueGenerale.php', $parameters);
    }


}
