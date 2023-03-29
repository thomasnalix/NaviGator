<?php

namespace Navigator\Test;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Modele\Repository\NoeudRoutierRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudRoutierService;
use PHPUnit\Framework\TestCase;

class NoeudRoutierServiceTest extends TestCase {

    private $service;
    private $noeudRoutierRepositoryMock;
    private $noeudCommuneRepositoryMock;


    protected function setUp(): void {
        parent::setUp();
        $this->noeudRoutierRepositoryMock = $this->createMock(NoeudRoutierRepositoryInterface::class);
        $this->noeudCommuneRepositoryMock = $this->createMock(NoeudCommuneRepositoryInterface::class);
        $this->service = new NoeudRoutierService($this->noeudRoutierRepositoryMock, $this->noeudCommuneRepositoryMock);
    }

    public function testGetNoeudRoutierProche() {
        $this->noeudRoutierRepositoryMock->method('getNoeudProche')->willReturn([
            "gid" => 6,
            "departement" => "2A",
            "nom_comm" => "Bonifacio",
            "lat" => "41.37078490043116",
            "long" => "9.206668253947754"
        ]);
        $result = $this->service->getNoeudRoutierProche(1,1);
        $this->assertEquals([
            "gid" => 6,
            "departement" => "2A",
            "nom_comm" => "Bonifacio",
            "lat" => "41.37078490043116",
            "long" => "9.206668253947754"
        ], $result);
    }

    public function testCalculerItineraire() {
        // De Billière à Cirès
        $this->noeudRoutierRepositoryMock->method('calculerItineraire')->willReturn([
            "0102000020E6100000020000000112967685E2E03F77EC2BA6DD674540482C166512E5E03F66EE437CE3674540"
        ]);
        $result = $this->service->calculerItineraire([98761, null]);
        $this->assertEquals([
            "0102000020E6100000020000000112967685E2E03F77EC2BA6DD674540482C166512E5E03F66EE437CE3674540"
        ], $result);
    }

    public function testCalculerItineraireException() {
        // Groix à Ploemeur (pas de route)
        $this->noeudRoutierRepositoryMock->method('calculerItineraire')->willReturn([]);
        $this->expectException(ServiceException::class);
        $this->service->calculerItineraire([]);

    }

}