<?php


namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class PlusCourtChemin
{

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

        $openSet = [$this->noeudRoutierDepart->getGid()];

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;

        $iteration = 0;
        $tempsFinale = 0;
        while (count($openSet) > 0) {

            $iteration++;
            $noeudRoutierGidCourant = null;
            $distanceMin = INF;
            foreach ($openSet as $gid) {
                if ($fScore[$gid] < $distanceMin) {
                    $distanceMin = $fScore[$gid];
                    $noeudRoutierGidCourant = $gid;
                }
            }

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudRoutierArrivee->getGid()) {
                echo "Itérations : " . $iteration . "<br>";
                echo "Temps final : " . $tempsFinale . "<br>";
                return $this->reconstruireChemin($cameFrom, $noeudRoutierGidCourant, $cost);
            }

            // TODO: trouver un moyen plus rapide pour supprimer un élément d'un tableau sans réindexer ?
            unset($openSet[array_search($noeudRoutierGidCourant, $openSet)]);

            $now = microtime(true);
            $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            if ($numDepartementNoeud === '' || !isset($this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant])) {
                $this->supprimerAncienDepartement();
                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierRegion($noeudRoutierGidCourant);
                $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
            }
            $tempsFinale += microtime(true) - $now;

            $neighbors = $this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant];

            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur_troncon'];
                $value = $gScore[$neighbor['noeud_gid']] ?? PHP_INT_MAX;
                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_gid']] = $neighbor['longueur_troncon'];

                    $gScore[$neighbor['noeud_gid']] = $tentativeGScore;

                    $fScore[$neighbor['noeud_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_coord']);
                    if (!array_key_exists($neighbor['noeud_gid'], $openSet)) {
                        $openSet[] = $neighbor['noeud_gid'];
                    }
                }
            }
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
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $distance = $earthRadius * $c;
        return $distance;

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

    private function supprimerAncienDepartement() {
        if (count($this->noeudsRoutierCache) > 5) {
            $key = array_keys($this->noeudsRoutierCache)[0];
            echo "Suppression de l'ancien département : " . $key . "<br>";
            array_shift($this->noeudsRoutierCache);
        }
    }

}
