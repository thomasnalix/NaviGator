<?php

namespace Navigator\Service;

interface PlusCourtCheminServiceInterface {
    /**
     * Calcule la distance la plus courte et l'itinéraire entre 2 ou plusieurs points
     * @return array|int[]|null
     */
    public function aStarDistance(array $noeudsRoutier): ?array;

    /**
     * Euristique euclidienne entre le noeud courant et le noeud d'arrivee qui calcule la distance entre les 2 noeuds
     * en utilisant la formule de Haversine
     * @param float $lat latitude du noeud courant
     * @param float $long longitude du noeud courant
     * @return float distance entre le noeud courant et le noeud d'arrivee
     */
    public function getHeuristiqueHaversine(float $latArrivee, float $longArrivee, float $lat, float $long): float;

    /**
     * @param array $cameFrom
     * @param int $current
     * @param array $cost
     * @param array $coordTrocon
     * @return array [distance, troncons]
     */
    public function reconstruireChemin(array $cameFrom, int $current, array $cost, array $coordTrocon, array $vitesse): array;

    public function getNumDepartement($noeudRoutierGidCourant): ?string;
}