<?php

namespace Navigator\Test;


use Navigator\Modele\Repository\HistoriqueRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\HistoriqueService;
use PHPUnit\Framework\TestCase;

class HistoriqueServiceTest extends TestCase {

    private $service;
    private $historiqueRepositoryMock;

    protected function setUp(): void {
        parent::setUp();
        $this->historiqueRepositoryMock = $this->createMock(HistoriqueRepositoryInterface::class);
        $this->service = new HistoriqueService($this->historiqueRepositoryMock);
    }

    public function testAjouterHistorique() {
        $this->historiqueRepositoryMock->method('ajouterHistorique')->willReturn(true);
        $this->service->ajouterHistorique(1, 1, '{"chemin" : ["0102000020E6100000020000000112967685E2E03F77EC2BA6DD674540482C166512E5E03F66EE437CE3674540"],"distance" : 5214.009999999999, "gas" : 8686,"nbCommunes" : 2,"noeudsList" : [],"nomCommuneArrivee" : "737047","nomCommuneDepart" : "832358","temps" : 77.0539436813188}');
        $this->assertTrue(true);
    }

    public function testAjouterHistoriqueException() {
        $this->historiqueRepositoryMock->method('ajouterHistorique')->willReturn(false);
        $this->expectException(ServiceException::class);
        $this->service->ajouterHistorique(1, 1, '{"chemin" : ["0102000020E6100000020000000112967685E2E03F77EC2BA6DD674540482C166512E5E03F66EE437CE3674540"],"distance" : 5214.009999999999, "gas" : 8686,"nbCommunes" : 2,"noeudsList" : [],"nomCommuneArrivee" : "737047","nomCommuneDepart" : "832358","temps" : 77.0539436813188}');
    }


}