<?php

namespace Navigator\Lib;

use Navigator\Service\NoeudRoutierService;
use SplPriorityQueue;

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
    private const EARTH_RADIUS = 6371;
    private ?string $numDepartementCourant;
    private PriorityQueue $openSet;

    private NoeudRoutierService $noeudRoutierService;

    public function __construct(private array $noeudsRoutier, NoeudRoutierService $noeudRoutierService) {
        $this->openSet = new PriorityQueue();
        $this->openSet->setExtractFlags(SplPriorityQueue::EXTR_DATA);
        $this->noeudRoutierService = $noeudRoutierService;
    }

    /**
     * Calcule la distance la plus courte et l'itinéraire entre 2 ou plusieurs points
     * @return array|int[]|null
     */
    function aStarDistance(): ?array {
        $cameFrom = $chemin = $coordTrocon = $numDepartement = [];
        $distance = $temps = $gas = 0;
        $gid = $this->noeudsRoutier[$this->index]->getGid();
        $cost[$gid] = 0;
        $vitesse[$gid] = 50;
        $gScore[$gid] = 0;
        $fScore[$gid] = 0;
        $gasPath[$gid] = 0;
        $this->openSet->insert($gid, 0);
        $visited[$gid] = true;

        while ($this->openSet->valid()) {
            $noeudRoutierGidCourant = $this->openSet->extract();
            unset($visited[$noeudRoutierGidCourant]);
            // Path found
            if ($noeudRoutierGidCourant == $this->noeudsRoutier[$this->index + 1]->getGid()) {
                $cheminReconstruit = $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon, $vitesse, $gasPath);
                $chemin = array_merge($chemin, $cheminReconstruit[1]);
                $distance += $cheminReconstruit[0];
                $temps += $cheminReconstruit[2];
                $gas += $cheminReconstruit[3];
                if ($this->index == count($this->noeudsRoutier) - 2) {
                    return [$distance, $chemin, $temps, $gas];
                } else {
                    $this->index++;
                    $cameFrom = $cost = $coordTrocon = $gScore = $fScore = $visited = $gasPath = []; // reset des variables
                    $this->openSet = new PriorityQueue();
                    $this->openSet->setExtractFlags(SplPriorityQueue::EXTR_DATA);
                    $gid = $this->noeudsRoutier[$this->index]->getGid();
                    $this->openSet->insert($gid, 0);
                    $cost[$this->noeudsRoutier[$this->index]->getGid()] = $gScore[$gid] = $fScore[$gid] = $gasPath[$gid] = 0;
                    $noeudRoutierGidCourant = $this->openSet->top();
                }
            }

            $this->numDepartementCourant = $this->getNumDepartement($noeudRoutierGidCourant);
            if (!isset($this->numDepartementCourant)) {
                $this->noeudsRoutierCache += $this->noeudRoutierService->getNoeudsRoutierDepartement($noeudRoutierGidCourant);
                $this->numDepartementCourant = $this->getNumDepartement($noeudRoutierGidCourant);
            }

            $neighbors = $this->noeudsRoutierCache[$this->numDepartementCourant][$noeudRoutierGidCourant];

            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $numDepartement[$neighbor['noeud_gid']] = $this->numDepartementCourant;
                    $cameFrom[$neighbor['noeud_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];
                    $gasPath[$neighbor['noeud_gid']] = 1;
                    $vitesse[$neighbor['noeud_gid']] = $neighbor['vitesse'];
                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_gid'];
                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;
                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristiqueEuclidienne($neighbor['noeud_coord_lat'], $neighbor['noeud_coord_long']);
                    if (!isset($visited[$neighbor['noeud_gid']]))
                        $this->openSet->insert($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                }
            }
        }
        return [-1, [], -1, -1];
    }


    /**
     * Euristique euclidienne entre le noeud courant et le noeud d'arrivee qui calcule la distance entre les 2 noeuds
     * à vol d'oiseau.
     * @param float $lat latitude du noeud courant
     * @param float $long longitude du noeud courant
     * @return float distance entre le noeud courant et le noeud d'arrivee
     */
    private function getHeuristiqueEuclidienne(float $lat, float $long): float {
        $latArrivee = $this->noeudsRoutier[$this->index + 1]->getLat();
        $longArrivee = $this->noeudsRoutier[$this->index + 1]->getLong();

        $dLat = deg2rad($latArrivee - $lat);
        $dLon = deg2rad($longArrivee - $long);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad($latArrivee)) * sin($dLon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * @param array $cameFrom
     * @param $current
     * @param $cost
     * @param $coordTrocon
     * @return array [distance, troncons]
     */
    private function reconstruireChemin(array $cameFrom, $current, $cost, $coordTrocon, $vitesse, $gas): array {
        $total_path = [$current];
        $trocons = [];
        $distance = 0;
        $tempsTotal = 0;
        $gasTotal = 0;
        while (array_key_exists($current, $cameFrom)) {
            $current = $cameFrom[$current];
            $total_path[] = $current;
        }
        foreach ($total_path as $gid) {
            $distance += $cost[$gid];
            $tempsTotal += $cost[$gid] / $vitesse[$gid];
            $gasTotal += $gas[$gid];
            $trocons[] = $coordTrocon[$gid] ?? null;
        }
        return [$distance, $trocons, $tempsTotal, $gasTotal];
    }

    private function getNumDepartement($noeudRoutierGidCourant): ?string {
        if (isset($this->numDepartementCourant) && isset($this->noeudsRoutierCache[$this->numDepartementCourant][$noeudRoutierGidCourant]))
            return $this->numDepartementCourant;
        foreach (array_keys($this->noeudsRoutierCache) as $numDepartement)
            if (isset($this->noeudsRoutierCache[$numDepartement][$noeudRoutierGidCourant]))
                return $numDepartement;
        return null;
    }

}
