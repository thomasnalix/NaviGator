<?php

namespace Navigator\Service;

use Navigator\Lib\PriorityQueue;
use SplPriorityQueue;
use function PHPUnit\Framework\isEmpty;

class PlusCourtCheminService implements PlusCourtCheminServiceInterface {

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
    public array $noeudsRoutierCache = [];

    /**
     * Index du noeud routier courant a traiter, commence a 0 et augmente de 1
     * a chaque fois qu'on a trouve le chemin le plus court entre 2 noeuds
     */
    private int $index = 0;
    private const EARTH_RADIUS = 6371;
    public ?string $numDepartementCourant;
    private PriorityQueue $openSet;

    public function __construct(private NoeudRoutierServiceInterface $noeudRoutierService) {
        $this->openSet = new PriorityQueue();
        $this->openSet->setExtractFlags(SplPriorityQueue::EXTR_DATA);
    }

    /**
     * Calcule la distance la plus courte et l'itinéraire entre 2 ou plusieurs points
     * @return array|int[]|null
     * @throws \Exception si aucun chemin trouvé
     */
    public function aStarDistance(array $noeudsRoutier): ?array {
        $cameFrom = $chemin = $coordTrocon = $numDepartement = [];
        $distance = $temps = 0;
        $gid = $noeudsRoutier[$this->index]->getGid();
        $cost[$gid] = 0;
        $vitesse[$gid] = 50;
        $gScore[$gid] = 0;
        $fScore[$gid] = 0;
        $this->openSet->insert($gid, 0);
        $visited[$gid] = true;

        $latArrivee = $noeudsRoutier[$this->index + 1]->getLat();
        $longArrivee = $noeudsRoutier[$this->index + 1]->getLong();

        while ($this->openSet->valid()) {
            $noeudRoutierGidCourant = $this->openSet->extract();
            unset($visited[$noeudRoutierGidCourant]);
            // Path found
            if ($noeudRoutierGidCourant == $noeudsRoutier[$this->index + 1]->getGid()) {
                $cheminReconstruit = $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon, $vitesse);
                $chemin = array_merge($chemin, $cheminReconstruit[1]);
                $distance += $cheminReconstruit[0];
                $temps += $cheminReconstruit[2];
                if ($this->index == count($noeudsRoutier) - 2) {
                    return [$distance, $chemin, $temps];
                } else {
                    $this->index++;
                    $latArrivee = $noeudsRoutier[$this->index + 1]->getLat();
                    $longArrivee = $noeudsRoutier[$this->index + 1]->getLong();
                    $cameFrom = $cost = $coordTrocon = $gScore = $fScore = $visited = []; // reset des variables
                    $this->openSet = new PriorityQueue();
                    $this->openSet->setExtractFlags(SplPriorityQueue::EXTR_DATA);
                    $gid = $noeudsRoutier[$this->index]->getGid();
                    $this->openSet->insert($gid, 0);
                    $cost[$noeudsRoutier[$this->index]->getGid()] = $gScore[$gid] = $fScore[$gid] = 0;
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
                    $vitesse[$neighbor['noeud_gid']] = $neighbor['vitesse'];
                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_gid'];
                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;
                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristiqueHaversine($latArrivee, $longArrivee, $neighbor['noeud_coord_lat'], $neighbor['noeud_coord_long']);
                    if (!isset($visited[$neighbor['noeud_gid']]))
                        $this->openSet->insert($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                }
            }
        }
        throw new \Exception('Aucun chemin trouvé !');
    }


    /**
     * Euristique euclidienne entre le noeud courant et le noeud d'arrivee qui calcule la distance entre les 2 noeuds
     * en utilisant la formule de Haversine
     * @param float $lat latitude du noeud courant
     * @param float $long longitude du noeud courant
     * @return float distance entre le noeud courant et le noeud d'arrivee
     */
    public function getHeuristiqueHaversine(float $latArrivee, float $longArrivee, float $lat, float $long): float {
        $dLat = deg2rad($latArrivee - $lat);
        $dLon = deg2rad($longArrivee - $long);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad($latArrivee)) * sin($dLon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * @param array $cameFrom
     * @param int $current
     * @param array $cost
     * @param array $coordTrocon
     * @return array [distance, troncons]
     */
    public function reconstruireChemin(array $cameFrom, int $current, array $cost, array $coordTrocon, array $vitesse): array {
        $total_path = [$current];
        $trocons = [];
        $distance = 0;
        $tempsTotal = 0;
        while (array_key_exists($current, $cameFrom)) {
            $current = $cameFrom[$current];
            $total_path[] = $current;
        }
        if (count($cost) > 1) {
            foreach ($total_path as $gid) {
                $distance += $cost[$gid];
                $tempsTotal += $cost[$gid] / $vitesse[$gid];
                $trocons[] = $coordTrocon[$gid] ?? null;
            }
        }
        return [$distance, $trocons, $tempsTotal];
    }

    public function getNumDepartement($noeudRoutierGidCourant): ?string {
        if (isset($this->numDepartementCourant) && isset($this->noeudsRoutierCache[$this->numDepartementCourant][$noeudRoutierGidCourant]))
            return $this->numDepartementCourant;
        foreach (array_keys($this->noeudsRoutierCache) as $numDepartement)
            if (isset($this->noeudsRoutierCache[$numDepartement][$noeudRoutierGidCourant]))
                return $numDepartement;
        return null;
    }

}