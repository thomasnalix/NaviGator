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
//        echo (" Appel de la fonction - " . date("H:i:s")) . "<br>";
//        $now = microtime(true);
        $parametres = [
            "pagetitle" => "Plus court chemin",
            "cheminVueBody" => "noeudCommune/plusCourtChemin.php",
        ];

        if (!empty($_POST)) {

            $communes = [];
            for($i = 0; $i < $_POST['nbField']; $i++) {
                $communes[] = $_POST["commune" . $i];
            }
//            $nomCommuneDepart = $_POST["commune0"];
//            $nomCommuneArrivee = $_POST["commune1"];
            $noeudCommuneRepository = new NoeudCommuneRepository();
            /** @var NoeudCommune $noeudCommuneDepart */

            $noeudCommunes = [];
            foreach ($communes as $commune) {
                $noeudCommunes[] = $noeudCommuneRepository->recupererPar(["nom_comm" => $commune])[0];
            }
//            $noeudCommuneDepart = $noeudCommuneRepository->recupererPar(["nom_comm" => $nomCommuneDepart])[0];
//            /** @var NoeudCommune $noeudCommuneArrivee */
//            $noeudCommuneArrivee = $noeudCommuneRepository->recupererPar(["nom_comm" => $nomCommuneArrivee])[0];

            $noeudRoutierRepository = new NoeudRoutierRepository();
//            $noeudRoutierDepartGid = $noeudRoutierRepository->recupererPar([
//                "id_rte500" => $noeudCommuneDepart->getId_nd_rte()
//            ])[0]->getGid();
//            $noeudRoutierArriveeGid = $noeudRoutierRepository->recupererPar([
//                "id_rte500" => $noeudCommuneArrivee->getId_nd_rte()
//            ])[0]->getGid();
//            $pcc = new PlusCourtChemin($noeudRoutierDepartGid, $noeudRoutierArriveeGid);
//            $distance = $pcc->calculer();

            $noeudRoutier = [];
            foreach ($noeudCommunes as $noeudCommune) {
                $noeudRoutier[] = $noeudRoutierRepository->recupererNoeudRoutier($noeudCommune->getId_nd_rte());
            }

//            $noeudRoutierDepart = $noeudRoutierRepository->recupererNoeudRoutier($noeudCommuneDepart->getId_nd_rte());
//            $noeudRoutierArrivee = $noeudRoutierRepository->recupererNoeudRoutier($noeudCommuneArrivee->getId_nd_rte());

            $pcc = new PlusCourtChemin($noeudRoutier);
//            $pcc = new PlusCourtChemin($noeudRoutierDepart, $noeudRoutierArrivee);
            $distance = $pcc->calculerAStar();

            $parametres["nomCommuneDepart"] = $communes[0];
            $parametres["nomCommuneArrivee"] = $communes[1];

//            $parametres["nomCommuneDepart"] = $nomCommuneDepart;
//            $parametres["nomCommuneArrivee"] = $nomCommuneArrivee;
            $parametres["distance"] = $distance[0];
            $parametres["chemin"] = $distance[1];
        }

//        echo " Fin de la fonction - " . date("H:i:s") . "<br>";
//        echo '=> Interval fonction : ' . (microtime(true) - $now) . ' secondes<br>';

        ControleurNoeudCommune::afficherVue('vueGenerale.php', $parametres);
    }


}
