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


    function calculerAStar(): float {

        $noeudRoutierRepository = new NoeudRoutierRepository();

        $this->noeudsRoutierCache = [];

        $openSet = new BinarySearchTree();
        $openSet->insert(new DataContainer($this->noeudRoutierDepart->getGid(), 0));

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;

        $iteration = 0;
        $tempsFinaleLoadUnlod = 0;
        $tempsFinaleVoisin = 0;
        $tempsF = 0;
        while (!$openSet->isEmpty()) {
            $iteration++;
            $nodeData = $openSet->getMinNode()->data;
            $noeudRoutierGidCourant = $nodeData->getGid();

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudRoutierArrivee->getGid()) {
                echo "Itérations : " . $iteration . "<br>";
                echo "Temps LOAD / UNLOAD departement : " . $tempsFinaleLoadUnlod . "<br>";
                echo "Temps calcul voisin : " . $tempsFinaleVoisin. "<br>";
                echo "Temps f : " . $tempsF. "<br>";
                $now = microtime(true);
                $chemin = $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost);
                echo "Temps reconstruire chemin : " . (microtime(true) - $now) . "<br>";
                return $chemin;
            }

            $openSet->delete($nodeData);

            $now = microtime(true);
            $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            if ($numDepartementNoeud === '') {
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierDepartement($noeudRoutierGidCourant);
                $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            }
            $tempsFinaleLoadUnlod += microtime(true) - $now;

            $neighbors = $this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant];

            $now = microtime(true);
            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];

                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;

                    $now = microtime(true);
                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_coord']);
                    $tempsF += microtime(true) - $now;
                    $dataContainer = new DataContainer($neighbor['noeud_gid'], $fScore[$neighbor['noeud_gid']]);
                    if (!$openSet->search($dataContainer))
                        $openSet->insert($dataContainer);
                }
            }
            $tempsFinaleVoisin += microtime(true) - $now;
        }
        echo "Itérations : " . $iteration . "<br>";
        return -1;
    }

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

    private function reconstruireChemin(array $cameFrom, $current, $cost): float {
        $total_path = [$current];
        $distance = 0;
        while (array_key_exists($current, $cameFrom)) {
            $current = $cameFrom[$current];
            $total_path[] = $current;
        }
        foreach ($total_path as $gid) {
            $distance += $cost[$gid];
        }
        return $distance;
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

    private function debugTableau($tableau)
    {
        echo "<pre>";
        print_r($tableau);
        echo "</pre>";
    }

}
