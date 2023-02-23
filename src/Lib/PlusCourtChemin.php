<?php


namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class PlusCourtChemin
{
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
//        private int $noeudRoutierDepartGid,
//        private int $noeudRoutierArriveeGid
        private NoeudRoutier $noeudRoutierDepart,
        private NoeudRoutier $noeudRoutierArrivee
    )
    {
    }

//     VERSION AVEC LE GIGA CACHE
//    public function calculer(bool $affichageDebug = false): float {
//        $noeudRoutierRepository = new NoeudRoutierRepository();
//
//        // Distance en km, table indexé par NoeudRoutier::gid
//        $this->distances = [$this->noeudRoutierDepartGid => 0];
//
//        $this->cheminChoisi = [];
//
//        $this->noeudsRoutierCache = [];
//
//        $this->noeudsALaFrontiere[$this->noeudRoutierDepartGid] = true;
//
//        $iteration = 0;
//        while (count($this->noeudsALaFrontiere) !== 0) {
//
//            $iteration++;
//            $noeudRoutierGidCourant = $this->noeudALaFrontiereDeDistanceMinimale();
//            // Enleve le noeud routier courant de la frontiere
//            unset($this->noeudsALaFrontiere[$noeudRoutierGidCourant]);
//
//            $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
//            if ($numDepartementNoeud === '' || !isset($this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant])) {
//                $this->supprimerAncienDepartement();
//                $this->noeudsRoutierCache += $noeudRoutierRepository->getNoeudsRoutierRegion($noeudRoutierGidCourant);
//                $numDepartementNoeud = $this->getNumDepartement($noeudRoutierGidCourant);
//                //echo "Node pas trouvé en cache : " . $noeudRoutierGidCourant . "<br>";
//                // taille en mémoire de $this->noeudsRoutierCache en mb
//                echo "Mémoire utilisé : " . (memory_get_usage() / 1024 / 1024) . " mb<br>";
//                echo "Nombre de noeuds en cache pour département " . $numDepartementNoeud . ": "  . count($this->noeudsRoutierCache[$numDepartementNoeud]) . " noeuds<br>";
//            }
//
//            foreach ($this->noeudsRoutierCache[$numDepartementNoeud][$noeudRoutierGidCourant] as $voisin) {
//                $noeudVoisinGid = $voisin["noeud_routier_gid"];
//                $distanceTroncon = $voisin["longueur"];
//
//                $distanceProposee = $this->distances[$noeudRoutierGidCourant] + $distanceTroncon;
//
//                if (!isset($this->distances[$noeudVoisinGid]) || $distanceProposee < $this->distances[$noeudVoisinGid]) {
//                    $this->cheminChoisi[] = $voisin["troncon_coord"];
//                    $this->distances[$noeudVoisinGid] = $distanceProposee;
//                    $this->noeudsALaFrontiere[$noeudVoisinGid] = true;
//                }
//            }
//
//            // Fini
//            if ($noeudRoutierGidCourant === $this->noeudRoutierArriveeGid) {
//                echo "Itérations : " . $iteration . "<br>";
//                return $this->distances[$noeudRoutierGidCourant];
//            }
//        }
//        return -1;
//    }


    function calculerAStar(): float {
        $openSet = [$this->noeudRoutierDepart->getGid()];

        $cameFrom = [];
        $cost[$this->noeudRoutierDepart->getGid()] = 0;

        $gScore[$this->noeudRoutierDepart->getGid()] = 0;

        $fScore[$this->noeudRoutierDepart->getGid()] = 0;

        $iteration = 0;
        $tempsFinale = 0;
        while (count($openSet) > 0) {

            $iteration++;
            $now = microtime(true);
            asort($fScore);
            $tempsFinale += microtime(true) - $now;
            $noeudRoutierGidCourant = null;
            foreach ($fScore as $gid => $distance) {
                if (in_array($gid, $openSet)) {
                    $noeudRoutierGidCourant = $gid;
                    break;
                }
            }

            // Path found
            if ($noeudRoutierGidCourant == $this->noeudRoutierArrivee->getGid()) {
                echo "Itérations : " . $iteration . "<br>";
                echo "Temps final : " . $tempsFinale . "<br>";
                return $this->reconstructPath($cameFrom, $noeudRoutierGidCourant, $cost);
            }

            unset($openSet[array_search($noeudRoutierGidCourant, $openSet)]);


            $noeudRoutierRepository = new NoeudRoutierRepository();
            $noeudRoutierCourant = $noeudRoutierRepository->getNoeudRoutier($noeudRoutierGidCourant);
            $neighbors = $noeudRoutierCourant->getVoisins();


            foreach ($neighbors as $neighbor) {
                $tentativeGScore = $gScore[$noeudRoutierGidCourant] + $neighbor['longueur'];
                $value = $gScore[$neighbor['noeud_routier_gid']] ?? PHP_INT_MAX;

                if ($tentativeGScore < $value) {
                    $cameFrom[$neighbor['noeud_routier_gid']] = $noeudRoutierGidCourant;
                    $cost[$neighbor['noeud_routier_gid']] = $neighbor['longueur'];

                    $gScore[$neighbor['noeud_routier_gid']] = $tentativeGScore;

                    $fScore[$neighbor['noeud_routier_gid']] = $tentativeGScore + $this->getHeuristique($neighbor['noeud_voisin_coord']);
                    if (!array_key_exists($neighbor['noeud_routier_gid'], $openSet)) {
                        $openSet[] = $neighbor['noeud_routier_gid'];
                    }
                }
            }



        }
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

    private function reconstructPath(array $cameFrom, $current, $cost): float {
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


    // VERSION SANS CACHE
    public function calculer(bool $affichageDebug = false): float
    {
        $noeudRoutierRepository = new NoeudRoutierRepository();

        // Distance en km, table indexé par NoeudRoutier::gid
        $this->distances = [$this->noeudRoutierDepartGid => 0];

        $this->cheminChoisi = [];

        $this->noeudsALaFrontiere[$this->noeudRoutierDepartGid] = true;

        $iteration = 0;
        while (count($this->noeudsALaFrontiere) !== 0) {

            $iteration++;
            $noeudRoutierGidCourant = $this->noeudALaFrontiereDeDistanceMinimale();
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

    private function debugTab($tab, $msg = "") {
        echo "<div><p>" . $msg . "</p>";
        print_r($tab);
        echo "</div><br>";
    }


}
