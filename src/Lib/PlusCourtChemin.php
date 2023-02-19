<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;

class PlusCourtChemin
{
    private array $distances;
    private array $noeudsALaFrontiere;

    public function __construct(
        private int $noeudRoutierDepartGid,
        private int $noeudRoutierArriveeGid
    ) {
    }

    public function calculer(bool $affichageDebug = false): float {
        // Genere un console.log() dans le navigateur
        $noeudRoutierRepository = new NoeudRoutierRepository();
        $noeud = [];

        // Distance en km, table indexé par NoeudRoutier::gid
        $this->distances = [$this->noeudRoutierDepartGid => 0];

        $this->noeudsALaFrontiere[$this->noeudRoutierDepartGid] = true;

        $itetarion = 0;

        while (count($this->noeudsALaFrontiere) !== 0) {

            $itetarion++;
            $noeudRoutierGidCourant = $this->noeudALaFrontiereDeDistanceMinimale();

            // Enleve le noeud routier courant de la frontiere
            unset($this->noeudsALaFrontiere[$noeudRoutierGidCourant]);

            /** @var NoeudRoutier $noeudRoutierCourant */
            $noeudRoutierCourant = $noeudRoutierRepository->recupererParClePrimaire($noeudRoutierGidCourant);
            $voisins = $noeudRoutierCourant->getVoisins();

            foreach ($voisins as $voisin) {
                $noeudVoisinGid = $voisin["noeud_routier_gid"];
                $distanceTroncon = $voisin["longueur"];

                $distanceProposee = $this->distances[$noeudRoutierGidCourant] + $distanceTroncon;

                if (!isset($this->distances[$noeudVoisinGid]) || $distanceProposee < $this->distances[$noeudVoisinGid]) {
                    $this->distances[$noeudVoisinGid] = $distanceProposee;
                    $this->noeudsALaFrontiere[$noeudVoisinGid] = true;
                }
            }

            // Fini
            if ($noeudRoutierGidCourant === $this->noeudRoutierArriveeGid) {
                echo "Itérations : " . $itetarion . "<br>";
                return $this->distances[$noeudRoutierGidCourant];
            }
        }
        return -1;
    }


    private function noeudALaFrontiereDeDistanceMinimale()
    {
        $now = date_create();

        $noeudRoutierDistanceMinimaleGid = -1;
        $distanceMinimale = PHP_INT_MAX;
        foreach ($this->noeudsALaFrontiere as $noeudRoutierGid => $valeur) {
            if ($this->distances[$noeudRoutierGid] < $distanceMinimale) {
                $noeudRoutierDistanceMinimaleGid = $noeudRoutierGid;
                $distanceMinimale = $this->distances[$noeudRoutierGid];
            }
        }

        //echo '=> Interval noeudALaFrontiereDeDistanceMinimale : ' . (date_diff(date_create(),$now))->format('%H:%I:%S') . '<br>';
        return $noeudRoutierDistanceMinimaleGid;
    }
}
