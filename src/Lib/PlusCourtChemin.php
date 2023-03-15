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
    private array $noeudsRoutierCache;

    public function __construct(
        private NoeudRoutier $noeudRoutierDepart,
        private NoeudRoutier $noeudRoutierArrivee
    ) { }


    function calculerAStar(): ?array {

        $nbIteration = 0;
        TimerUtils::startTimer("total");

        $noeudRoutierRepository = new NoeudRoutierRepository();

        $this->noeudsRoutierCache = [];

        $openSet = new BinarySearchTree();
        $openSet->insert(new DataContainer($this->noeudRoutierDepart->getGid(), 0));

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;
        $coordTrocon = [];

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;

        while (!$openSet->isEmpty()) {
            TimerUtils::startOrRestartTimer("getMinNode");
            $nodeData = $openSet->getMinNode()->data;
            TimerUtils::pauseTimer("getMinNode");
            $noeudRoutierGidCourant = $nodeData->getGid();

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudRoutierArrivee->getGid()) {
                TimerUtils::stopAllTimers();
                TimerUtils::printAllTimers();
                echo "Nb iteration: $nbIteration<br>";
                return $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost, $coordTrocon);
            }

            TimerUtils::startOrRestartTimer("deleteNode");
            $openSet->delete($nodeData);
            TimerUtils::pauseTimer("deleteNode");

            TimerUtils::startOrRestartTimer("loadDepartement");
            $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            if ($numDepartementNoeud === '') {
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierDepartement($noeudRoutierGidCourant);
                $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            }
            TimerUtils::pauseTimer("loadDepartement");

            $neighbors = $this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant];

            TimerUtils::startOrRestartTimer("voisin");
            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];

                    $coordTrocon[$neighbor['noeud_gid']] = $neighbor['troncon_coord'];

                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;

                    TimerUtils::startOrRestartTimer("heuristique");
                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_coord']);
                    TimerUtils::pauseTimer("heuristique");

                    $dataContainer = new DataContainer($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                    TimerUtils::startOrRestartTimer("searchNode");
                    $search = $openSet->search($dataContainer);
                    TimerUtils::pauseTimer("searchNode");
                    if (!$search) {
                        TimerUtils::startOrRestartTimer("insertNode");
                        $openSet->insert($dataContainer);
                        TimerUtils::pauseTimer("insertNode");
                    }
                }
            }
            TimerUtils::pauseTimer("voisin");
            $nbIteration++;
        }
        return null;
    }

    /**
     * @param $noeud
     * @return float
     */
    private function getHeuristique($noeud): float {
        $coordsArrivee = explode(";", $this->noeudRoutierArrivee->getCoords());
        $coordsPoints = explode(";", $noeud);

        $lat2 = $coordsArrivee[1];
        $lon2 = $coordsArrivee[0];
        $lat1 = $coordsPoints[1];
        $lon1 = $coordsPoints[0];

        $earthRadius = 6371; // rayon de la Terre en kilomètres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $sinLat = sin($dLat / 2);
        $sinLon = sin($dLon / 2);
        $a = $sinLat * $sinLat + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * $sinLon * $sinLon;
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

    private function getNumDepartement($noeudRoutierGidCourant) {
        $numDepartement = '';
        for ($i = 0; $i < count($this->noeudsRoutierCache); $i++) {
            $key = array_keys($this->noeudsRoutierCache)[$i];
            if (isset($this->noeudsRoutierCache[$key][$noeudRoutierGidCourant]))
                return $key;
        }
        return $numDepartement;
    }

    private function debugTableau($tableau) {
        echo "<pre>";
        print_r($tableau);
        echo "</pre>";
    }

}
