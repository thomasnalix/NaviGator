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

    private ?string $numDepartementCourant;

    private DataStructure $openSet;

public function __construct(
    private NoeudRoutier $noeudRoutierDepart,
    private NoeudRoutier $noeudRoutierArrivee
) {
    $this->openSet = new BinarySearchTree();
}

    function calculerAStar(): ?array {

        $noeudRoutierRepository = new NoeudRoutierRepository();

        $this->openSet->insert(new DataContainer($this->noeudRoutierDepart->getGid(), 0));

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;
        $coordTrocon = [];

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;

        while (!$this->openSet->isEmpty()) {
            $nodeData = $this->openSet->getMinNode();
            $noeudRoutierGidCourant = $nodeData->getGid();

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudRoutierArrivee->getGid()) {
                return $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon);
            }

            $this->numDepartementCourant = $this->getNumDepartement($noeudRoutierGidCourant);
            if (!isset($this->numDepartementCourant)) {
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierDepartement($noeudRoutierGidCourant);
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

                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_coord'];

                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;

                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_coord_lat'],$neighbor['noeud_coord_long']);

                    $dataContainer = new DataContainer($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                    //TimerUtils::startOrRestartTimer("searchNode");
                    $search = $this->openSet->search($dataContainer);
                    if (!$search) {
                        $this->openSet->insert($dataContainer);
                    }
                }
            }
        }
        return [-1, -1];
    }

    /**
     * @param $noeud
     * @return float
     */
    private function getHeuristique(float $lat, float $long): float {
        $latArrivee = $this->noeudRoutierArrivee->getLat();
        $longArrivee = $this->noeudRoutierArrivee->getLong();
        $latCourant = $lat;
        $longCourant = $long;

        $earthRadius = 6371; // rayon de la Terre en kilomètres
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
    private function reconstruireChemin(array $cameFrom, $current, $cost, $coordTrocon): array {
        $total_path = [$current];
        $trocons = [];
        $distance = 0;
        while (array_key_exists($current, $cameFrom)) {
            $current = $cameFrom[$current];
            $total_path[] = $current;
        }
        foreach ($total_path as $gid) {
            $distance += $cost[$gid];
            $trocons[] = $coordTrocon[$gid] ?? null;
        }
        return [$distance, $trocons];
    }

    private function getNumDepartement($noeudRoutierGidCourant) : ?string {
        foreach (array_keys($this->noeudsRoutierCache) as $numDepartement)
            if (isset($this->noeudsRoutierCache[$numDepartement][$noeudRoutierGidCourant]))
                return $numDepartement;
        return null;
    }

}
