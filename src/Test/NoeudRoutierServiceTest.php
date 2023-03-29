<?php

namespace Navigator\Test;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Modele\Repository\NoeudRoutierRepositoryInterface;
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

}