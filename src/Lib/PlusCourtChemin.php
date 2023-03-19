<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class PlusCourtChemin {

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
    private array $noeudsRoutierCache = [];

    /**
     * Index du noeud routier courant a traiter, commence a 0 et augmente de 1
     * a chaque fois qu'on a trouve le chemin le plus court entre 2 noeuds
     */
    private int $index = 0;
    private ?string $numDepartementCourant;
    private DataStructure $openSet;

    public function __construct(private array $noeudsRoutier) {
        $this->openSet = new BinarySearchTree();
    }

    /**
     * Calcule la distance la plus courte et l'itinéraire entre 2 ou plusieurs points
     * @return array|int[]|null
     */
    function aStarDistance(): ?array {
        $noeudRoutierRepository = new NoeudRoutierRepository();
        $cameFrom = $chemin = $coordTrocon= [];
        $distance = $temps = 0;
        $cost[$this->noeudsRoutier[$this->index]->getGid()] = 0;
        $vitesse[$this->noeudsRoutier[$this->index]->getGid()] = 50;
        $gScore[$this->noeudsRoutier[$this->index]->getGid()] = 0;
        $fScore[$this->noeudsRoutier[$this->index]->getGid()] = 0;
        $this->openSet->insert(new DataContainer($this->noeudsRoutier[$this->index]->getGid(), 0));

        while (!$this->openSet->isEmpty()) {
            $nodeData = $this->openSet->getMinNode();
            $noeudRoutierGidCourant = $nodeData->getGid();

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudsRoutier[$this->index+1]->getGid()) {
                $cheminReconstruit = $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon,$vitesse);
                $chemin = array_merge($chemin, $cheminReconstruit[1]);
                $distance += $cheminReconstruit[0];
                $temps += $cheminReconstruit[2];
                if ($this->index == count($this->noeudsRoutier) - 2) {
                    return [$distance, $chemin, $temps];
                } else {
                    $this->index++;
                    $cameFrom = $cost = $coordTrocon = $gScore = $fScore = []; // reset des variables
                    $this->openSet = new BinarySearchTree();
                    $this->openSet->insert(new DataContainer($this->noeudsRoutier[$this->index]->getGid(), 0));
                    $cost[$this->noeudsRoutier[$this->index]->getGid()] = $gScore[$this->noeudsRoutier[$this->index]->getGid()] = $fScore[$this->noeudsRoutier[$this->index]->getGid()] = 0;
                    $noeudRoutierGidCourant = $this->openSet->getMinNode()->getGid();
                }
            }

            $this->numDepartementCourant = $this->getNumDepartement($noeudRoutierGidCourant);
            if (!isset($this->numDepartementCourant)) {
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierDepartementTime($noeudRoutierGidCourant);
                $this->numDepartementCourant = $this->getNumDepartement($noeudRoutierGidCourant);
            }

            $this->openSet->delete($nodeData);
            $neighbors = $this->noeudsRoutierCache[$this->numDepartementCourant][$noeudRoutierGidCourant];

            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];
                    $vitesse[$neighbor['noeud_gid']] = $neighbor['vitesse'];
                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_coord'];
                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;
                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristiqueEuclidienne($neighbor['noeud_coord_lat'],$neighbor['noeud_coord_long']);

                    $dataContainer = new DataContainer($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                    $search = $this->openSet->search($dataContainer);
                    if (!$search)
                        $this->openSet->insert($dataContainer);
                }
            }
        }
        return [-1, -1];
    }


    /**
     * Euristique euclidienne entre le noeud courant et le noeud d'arrivee qui calcule la distance entre les 2 noeuds
     * à vol d'oiseau.
     * @param float $lat latitude du noeud courant
     * @param float $long longitude du noeud courant
     * @return float distance entre le noeud courant et le noeud d'arrivee
     */
    private function getHeuristiqueEuclidienne(float $lat, float $long): float {
        static $earthRadius = 6371; // rayon de la Terre en kilomètres
        static $latArrivee, $longArrivee;

        if (!isset($latArrivee)) {
            $latArrivee = $this->noeudsRoutier[$this->index+1]->getLat();
            $longArrivee = $this->noeudsRoutier[$this->index+1]->getLong();
        }

        $latCourant = $lat;
        $longCourant = $long;

        $dLat = deg2rad($latArrivee - $latCourant);
        $dLon = deg2rad($longArrivee - $longCourant);
        $sinLat = sin($dLat / 2);
        $sinLon = sin($dLon / 2);
        $a = $sinLat * $sinLat + cos(deg2rad($latCourant)) * cos(deg2rad($latArrivee)) * $sinLon * $sinLon;
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c; // distance en kilomètres
    }

    /**
     * @param array $cameFrom
     * @param $current
     * @param $cost
     * @param $coordTrocon
     * @return array [distance, troncons]
     */
    private function reconstruireChemin(array $cameFrom, $current, $cost, $coordTrocon, $vitesse): array {
        $total_path = [$current];
        $trocons = [];
        $distance = 0;
        $tempsTotal = 0;
        while (array_key_exists($current, $cameFrom)) {
            $current = $cameFrom[$current];
            $total_path[] = $current;
        }
        foreach ($total_path as $gid) {
            $distance += $cost[$gid];
            $tempsTotal += $cost[$gid] / $vitesse[$gid];
            $trocons[] = $coordTrocon[$gid] ?? null;
        }
        return [$distance, $trocons, $tempsTotal];
    }

    private function getNumDepartement($noeudRoutierGidCourant) : ?string {
        foreach (array_keys($this->noeudsRoutierCache) as $numDepartement)
            if (isset($this->noeudsRoutierCache[$numDepartement][$noeudRoutierGidCourant]))
                return $numDepartement;
        return null;
    }

}
