<?php

namespace Navigator\Test;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudCommuneService;
use PHPUnit\Framework\TestCase;

class NoeudCommuneServiceTest extends TestCase {

    private $service;
    private $noeudCommuneRepositoryMock;

    protected function setUp(): void {
        parent::setUp();
        $this->noeudCommuneRepositoryMock = $this->createMock(NoeudCommuneRepositoryInterface::class);
        $this->service = new NoeudCommuneService($this->noeudCommuneRepositoryMock);
    }

    public function testGetCoordNoeudCommune() {
        $this->noeudCommuneRepositoryMock->method('getCoordNoeudCommune')->willReturn([
            "lat" => "43.59917864959453",
            "long" => "3.894125217456986"
        ]);

        $result = $this->service->getCoordNoeudCommune("Montpellier");
        $this->assertEquals([
            "lat" => "43.59917864959453",
            "long" => "3.894125217456986"
        ], $result);
    }

    public function testGetCoordNoeudCommuneException() {
        $this->noeudCommuneRepositoryMock->method('getCoordNoeudCommune')->willReturn(null);
        $this->expectException(ServiceException::class);
        $this->service->getCoordNoeudCommune("pellierMont");
    }

    public function testGetNomCommunes() {
        $this->noeudCommuneRepositoryMock->method('getNomCommunes')->willReturn([
            "Montpellier (34172)",
            "Montpellier-de-Médillan (17244)",
            "Murviel-lès-Montpellier (34179)"
        ]);

        $result = $this->service->getNomCommunes("Montpel");
        $this->assertEquals([
            "Montpellier (34172)",
            "Montpellier-de-Médillan (17244)",
            "Murviel-lès-Montpellier (34179)"
        ], $result);
    }

    public function testGetNomCommunesEmpty() {
        $this->noeudCommuneRepositoryMock->method('getNomCommunes')->willReturn([]);
        $this->service->getNomCommunes("PellierMont");
        $this->assertEquals([], $this->service->getNomCommunes("PellierMont"));
    }


}