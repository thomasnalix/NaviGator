<?php

namespace Navigator\Service;


interface NoeudRoutierServiceInterface {

    public function getNoeudRoutierProche(float $lat, float $long): array;

    public function getVillesItinary(int $nbField, array $communesList): array;

    public function calculerItineraire(array $tronconsGid): array;

    public function getNoeudsRoutierDepartement(int $noeudRoutierGid): array;

    public function getCoordNoeudCommune(string $nomVille): array;

    public function getNomCommunes(string $nomCommune): array;

    public function getCoordNoeudByGid(int $commune);

}