<?php

namespace Navigator\Test;

use Navigator\Modele\DataObject\Historique;
use Navigator\Modele\Repository\TrajetsRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\TrajetsService;
use PHPUnit\Framework\TestCase;

class TrajetServiceTest extends TestCase {

    private $service;
    private $trajetRepositoryMock;

    protected function setUp(): void {
        parent::setUp();
        $this->trajetRepositoryMock = $this->createMock(TrajetsRepositoryInterface::class);
        $this->service = new TrajetsService($this->trajetRepositoryMock);
    }

    public function testGetTrajet() {
        $this->trajetRepositoryMock->method('getTrajet')->willReturn(
            '{"chemin" : ["0102000020E6100000020000000112967685E2E03F77EC2BA6DD674540482C166512E5E03F66EE437CE3674540"],"distance" : 5214.009999999999, "gas" : 8686,"nbCommunes" : 2,"noeudsList" : [],"nomCommuneArrivee" : "737047","nomCommuneDepart" : "832358","temps" : 77.0539436813188}');
        $result = $this->service->getTrajet(1);
        $this->assertEquals(
            '{"chemin" : ["0102000020E6100000020000000112967685E2E03F77EC2BA6DD674540482C166512E5E03F66EE437CE3674540"],"distance" : 5214.009999999999, "gas" : 8686,"nbCommunes" : 2,"noeudsList" : [],"nomCommuneArrivee" : "737047","nomCommuneDepart" : "832358","temps" : 77.0539436813188}',
            $result
        );
    }

    public function testGetTrajetException() {
        $this->trajetRepositoryMock->method('getTrajet')->willReturn(null);
        $this->expectException(ServiceException::class);
        $this->service->getTrajet(90);
    }

    public function testGetHistoryException() {
        $this->trajetRepositoryMock->method('getHistory')->willReturn(null);
        $this->expectException(ServiceException::class);
        $this->service->getHistory(90);
    }

    public function testGetHistory() {
        $this->trajetRepositoryMock->method('getHistory')->willReturn(
            [new Historique(1,['La Quinte','Saint-Luc'])]);
        $result = $this->service->getHistory('userTest');
        $this->assertEquals(
            [new Historique(1,['La Quinte','Saint-Luc'])],
            $result
        );
    }
}