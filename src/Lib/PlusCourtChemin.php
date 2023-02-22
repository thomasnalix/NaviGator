<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class PlusCourtChemin {
    private array $distances;
    private array $noeudsALaFrontiere;
    private array $cheminChoisi;

    /**
     * Construit comme suit:
     * [
     *     numDepartement => [
     *         gid => [
     *             ...
     *         ],
     *         gid2 => [
     *             ...
     *         ]
     *     ],
     *     numDepartement2 => [ ... ]
     * ]
     */
    private array $noeudsRoutierCache;

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

            $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            if ($numDepartementNoeud === '' || !isset($this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant])) {
                $this->supprimerAncienDepartement();
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierRegion($noeudRoutierGidCourant);
                $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
                //echo "Node pas trouvé en cache : " . $noeudRoutierGidCourant . "<br>";
                // taille en mémoire de $this->noeudsRoutierCache en mb
                echo "Mémoire utilisé : " . (memory_get_usage() / 1024 / 1024) . " mb<br>";
                echo "Nombre de noeuds en cache pour département " . $numDepartementNoeud . ": "  . count($this->noeudsRoutierCache[$numDepartementNoeud]) . " noeuds<br>";
            }

            foreach ($this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant] as $voisin) {
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

    private function getNumDepartement($noeudRoutierGidCourant) {
        $numDepartement = '';
        for ($i = 0; $i < count($this->noeudsRoutierCache); $i++) {
            $key = array_keys($this->noeudsRoutierCache)[$i];
            if (isset($this->noeudsRoutierCache[$key][$noeudRoutierGidCourant]))
                $numDepartement = $key;
        }
        return $numDepartement;
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

    private function supprimerAncienDepartement() {
        if (count($this->noeudsRoutierCache) > 5) {
            $key = array_keys($this->noeudsRoutierCache)[0];
            echo "Suppression de l'ancien département : " . $key . "<br>";
            array_shift($this->noeudsRoutierCache);
        }
    }
}
