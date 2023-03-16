<?php


namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;
use Spatie\Async\Pool;

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

    private ?string $numDepartementCourant = null;

    public static $lastLoadedDepartement = null;

public function __construct(
        private NoeudRoutier $noeudRoutierDepart,
        private NoeudRoutier $noeudRoutierArrivee
    ) { }


    function calculerAStar(): ?array {

        $now = microtime(true);
        $cumul = 0;
        $voisin = 0;
        $nowSearch = 0;
        $nowInsert = 0;
        $nowDelete = 0;

        $nbIteration = 0;
        TimerUtils::startTimer("total");

        $noeudRoutierRepository = new NoeudRoutierRepository();

        $openSet = new BinarySearchTree();
        $openSet->insert(new DataContainer($this->noeudRoutierDepart->getGid(), 0));

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;
        $coordTrocon = [];

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;
        $nowHeuristique = 0;
        $pool = Pool::create();

        while (!$openSet->isEmpty()) {
            //TimerUtils::startOrRestartTimer("getMinNode");
            $nodeData = $openSet->getMinNode();
            //TimerUtils::pauseTimer("getMinNode");
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
                echo "Search : " . $nowSearch . "s<br>";
                echo "Insert : " . $nowInsert . "s<br>";
                echo "Delete : " . $nowDelete . "s<br>";
                return $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon);
            }

            //TimerUtils::startOrRestartTimer("deleteNode");
            $now8 = microtime(true);
            $openSet->delete($nodeData);
            $nowDelete += microtime(true) - $now8;
            //TimerUtils::pauseTimer("deleteNode");

//            TimerUtils::startOrRestartTimer("loadDepartement");
            $now2 = microtime(true);
            $this->numDepartementCourant = $this->getNumDepartement($noeudRoutierGidCourant);
            if (!isset($this->numDepartementCourant)) {
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierDepartement($noeudRoutierGidCourant);
                $this->numDepartementCourant = PlusCourtChemin::$lastLoadedDepartement;
                $this->loadedDepartements[] = $this->numDepartementCourant;
            }
            $cumul += microtime(true) - $now2;


//            TimerUtils::pauseTimer("loadDepartement");
            $neighbors = $this->noeudsRoutierCache[$this->numDepartementCourant][$noeudRoutierGidCourant];

            //TimerUtils::startOrRestartTimer("voisin");
            $now3 = microtime(true);
            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];

                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_coord'];

                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;

//                    $pool->add(function () use ($neighbor, $tentativeGScore, $openSet) {
                        //TimerUtils::startOrRestartTimer("heuristique");
                         $now4 = microtime(true);
                        $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_coord_lat'],$neighbor['noeud_coord_long']);
                        $nowHeuristique += microtime(true) - $now4;
                        //TimerUtils::pauseTimer("heuristique");

                        $dataContainer = new DataContainer($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                        //TimerUtils::startOrRestartTimer("searchNode");
                        $now5 = microtime(true);
                        $search = $openSet->search($dataContainer);
                        $nowSearch += microtime(true) - $now5;
                        //TimerUtils::pauseTimer("searchNode");
                        if (!$search) {
                            //TimerUtils::startOrRestartTimer("insertNode");
                            $now6 = microtime(true);
                            $openSet->insert($dataContainer);
                            $nowInsert += microtime(true) - $now6;
                            //TimerUtils::pauseTimer("insertNode");
                        }
                   // });
                }
            }
            //$pool->wait();
            $voisin += microtime(true) - $now3;
            //TimerUtils::pauseTimer("voisin");
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
        foreach ($this->loadedDepartements as $departement)
            if (isset($this->noeudsRoutierCache[$departement][$noeudRoutierGidCourant]))
                return $departement;
        return null;
    }

    private function debugTableau($tableau) {
        echo "<pre>";
        print_r($tableau);
        echo "</pre>";
    }

}
