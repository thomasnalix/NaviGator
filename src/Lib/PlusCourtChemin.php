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
    private array $loadedDepartements = [];

    private ?string $numDepartementCourant;
    public static string $lastLoadedDepartement;

    private DataStructure $openSet;

public function __construct(
    private NoeudRoutier $noeudRoutierDepart,
    private NoeudRoutier $noeudRoutierArrivee
) {
    $this->openSet = new BinarySearchTree();
}

    function calculerAStar(): ?array {

        $now = microtime(true);
        $cumul = 0;
        $voisin = 0;
        $nowSearch = 0;
        $nowInsert = 0;
        $nowMin = 0;
        $nowDelete = 0;

        $nbIteration = 0;
        TimerUtils::startTimer("total");

        $noeudRoutierRepository = new NoeudRoutierRepository();

        $this->openSet->insert(new DataContainer($this->noeudRoutierDepart->getGid(), 0));

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;
        $coordTrocon = [];

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;
        $nowHeuristique = 0;

        while (!$this->openSet->isEmpty()) {
            $now0 = microtime(true);
            $nodeData = $this->openSet->getMinNode();
            $nowMin += microtime(true) - $now0;
            $noeudRoutierGidCourant = $nodeData->getGid();

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudRoutierArrivee->getGid()) {
//                TimerUtils::stopAllTimers();
//                TimerUtils::printAllTimers();
                echo "total: " . (microtime(true) - $now) . "s<br>";
                echo "cumulBD : " . $cumul . "s<br>";
                echo "voisin : " . $voisin . "s<br>";
                echo "nbIteration : " . $nbIteration . "<br>";
                echo "Heuruistique : " . $nowHeuristique . "s<br>";
                echo "Min : " . $nowMin . "s<br>";
                echo "Insert : " . $nowInsert . "s<br>";
                echo "Search : " . $nowSearch . "s<br>";
                echo "Delete : " . $nowDelete . "s<br>";
                return $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon);
            }

            $now2 = microtime(true);
            $this->numDepartementCourant = $cameFrom[$noeudRoutierGidCourant][1] ?? null;
            if (!isset($this->numDepartementCourant) || !isset($this->loadedDepartements[$this->numDepartementCourant])) {

                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierDepartement($noeudRoutierGidCourant);
                $this->numDepartementCourant = PlusCourtChemin::$lastLoadedDepartement;
                $this->loadedDepartements[] = $this->numDepartementCourant;
            }
            $cumul += microtime(true) - $now2;

            $now0 = microtime(true);
            $this->openSet->delete($nodeData);
            $nowDelete += microtime(true) - $now0;

            $neighbors = $this->noeudsRoutierCache[$this->numDepartementCourant][$noeudRoutierGidCourant];

            $now3 = microtime(true);
            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_gid']] = [$noeudRoutierGidCourant, $neighbor['num_departement']];
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];

                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_coord'];

                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;

                    $now4 = microtime(true);
                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_coord_lat'],$neighbor['noeud_coord_long']);
                    $nowHeuristique += microtime(true) - $now4;

                    $dataContainer = new DataContainer($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                    //TimerUtils::startOrRestartTimer("searchNode");
                    $now5 = microtime(true);
                    $search = $this->openSet->search($dataContainer);
                    $nowSearch += microtime(true) - $now5;
                    //TimerUtils::pauseTimer("searchNode");
                    if (!$search) {
                        //TimerUtils::startOrRestartTimer("insertNode");
                        $now6 = microtime(true);
                        $this->openSet->insert($dataContainer);
                        $nowInsert += microtime(true) - $now6;
                        //TimerUtils::pauseTimer("insertNode");
                    }
                }
            }
            $voisin += microtime(true) - $now3;
            $nbIteration++;
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
        while (isset($cameFrom[$current])) {
            $total_path[] = $cameFrom[$current][0];
            $current = $cameFrom[$current][0];
        }
        foreach ($total_path as $gid) {
            $distance += $cost[$gid];
            $trocons[] = $coordTrocon[$gid] ?? null;
        }
        return [$distance, $trocons];
    }

}
