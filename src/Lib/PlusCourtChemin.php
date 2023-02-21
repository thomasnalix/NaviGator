<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class PlusCourtChemin {
    private array $distances;
    private array $noeudsALaFrontiere;
    private array $cheminChoisi;

    private array $noeudsRoutierCache; // gid => [voisins]

    public function __construct(
        private int $noeudRoutierDepartGid,
        private int $noeudRoutierArriveeGid
    ) { }

    // VERSION AVEC LE GIGA CACHE
    /*public function calculer(bool $affichageDebug = false): float {
        $noeudRoutierRepository = new NoeudRoutierRepository();

        // Distance en km, table indexé par NoeudRoutier::gid
        $this->distances = [$this->noeudRoutierDepartGid => 0];

        $this->cheminChoisi = [];

        $this->noeudsRoutierCache = [];

        $this->noeudsALaFrontiere[$this->noeudRoutierDepartGid] = true;

        $iteration = 0;
        while (count($this->noeudsALaFrontiere) !== 0) {

            $iteration++;
            $noeudRoutierGidCourant = $this->noeudALaFrontiereDeDistanceMinimale();;
            // Enleve le noeud routier courant de la frontiere
            unset($this->noeudsALaFrontiere[$noeudRoutierGidCourant]);

            // si pas en cache on l'ajoute
            if (!isset($this->noeudsRoutierCache[$noeudRoutierGidCourant])) {
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierRegion($noeudRoutierGidCourant);
                echo "Node pas trouvé en cache : " . $noeudRoutierGidCourant . "<br>";
            }

            foreach ($this->noeudsRoutierCache[$noeudRoutierGidCourant] as $voisin) {
                $noeudVoisinGid = $voisin["noeud_routier_gid"];
                $distanceTroncon = $voisin["longueur"];

                $distanceProposee = $this->distances[$noeudRoutierGidCourant] + $distanceTroncon;

                if (!isset($this->distances[$noeudVoisinGid]) || $distanceProposee < $this->distances[$noeudVoisinGid]) {
                    $this->cheminChoisi[] = $voisin["troncon_coord"];
                    $this->distances[$noeudVoisinGid] = $distanceProposee;
                    $this->noeudsALaFrontiere[$noeudVoisinGid] = true;
                }
            }

            // Fini
            if ($noeudRoutierGidCourant === $this->noeudRoutierArriveeGid) {
                echo "Itérations : " . $iteration . "<br>";
                return $this->distances[$noeudRoutierGidCourant];
            }
        }
        return -1;
    }*/

    // VERSION SANS CACHE
    public function calculer(bool $affichageDebug = false): float {
        $noeudRoutierRepository = new NoeudRoutierRepository();

        // Distance en km, table indexé par NoeudRoutier::gid
        $this->distances = [$this->noeudRoutierDepartGid => 0];

        $this->cheminChoisi = [];

        $this->noeudsALaFrontiere[$this->noeudRoutierDepartGid] = true;

        $iteration = 0;
        while (count($this->noeudsALaFrontiere) !== 0) {

            $iteration++;
            $noeudRoutierGidCourant = $this->noeudALaFrontiereDeDistanceMinimale();;
            // Enleve le noeud routier courant de la frontiere
            unset($this->noeudsALaFrontiere[$noeudRoutierGidCourant]);

            // NoeudRoutier $noeudRoutierCourant
            // $noeudRoutierCourant = $noeudRoutierRepository->recupererParClePrimaire($noeudRoutierGidCourant);
            $noeudRoutierCourant = $noeudRoutierRepository->getNoeudRoutier($noeudRoutierGidCourant);
            $voisins = $noeudRoutierCourant->getVoisins();
            foreach ($voisins as $voisin) {
                $noeudVoisinGid = $voisin["noeud_routier_gid"];
                $distanceTroncon = $voisin["longueur"];

                $distanceProposee = $this->distances[$noeudRoutierGidCourant] + $distanceTroncon;

                if (!isset($this->distances[$noeudVoisinGid]) || $distanceProposee < $this->distances[$noeudVoisinGid]) {
                    $this->cheminChoisi[] = $voisin["troncon_coord"];
                    $this->distances[$noeudVoisinGid] = $distanceProposee;
                    $this->noeudsALaFrontiere[$noeudVoisinGid] = true;
                }
            }

            // Fini
            if ($noeudRoutierGidCourant === $this->noeudRoutierArriveeGid) {
                echo "Itérations : " . $iteration . "<br>";
                return $this->distances[$noeudRoutierGidCourant];
            }
        }
        return -1;
    }

    private function noeudALaFrontiereDeDistanceMinimale() {
        $noeudRoutierDistanceMinimaleGid = -1;
        $distanceMinimale = PHP_INT_MAX;
        foreach ($this->noeudsALaFrontiere as $noeudRoutierGid => $valeur) {
            if ($this->distances[$noeudRoutierGid] < $distanceMinimale) {
                $noeudRoutierDistanceMinimaleGid = $noeudRoutierGid;
                $distanceMinimale = $this->distances[$noeudRoutierGid];
            }
        }
        return $noeudRoutierDistanceMinimaleGid;
    }

    public function getCheminChoisi(): array {
        return $this->cheminChoisi;
    }
}
