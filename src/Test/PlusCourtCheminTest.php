<?php

namespace Navigator\Test;

use Navigator\Modele\DataObject\NoeudRoutier;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudRoutierServiceInterface;
use Navigator\Service\PlusCourtCheminService;
use PHPUnit\Framework\TestCase;

class PlusCourtCheminTest extends TestCase {

    private $lib;
    private $noeudRoutierService;

    protected function setUp(): void {
        parent::setUp();
        $this->noeudRoutierService = $this->createMock(NoeudRoutierServiceInterface::class);
        $this->lib = new PlusCourtCheminService($this->noeudRoutierService);
    }

    public function testGetNumDepartementNotFound() {
        $this->assertNull($this->lib->getNumDepartement(0));
    }

    public function testGetNumDepartementFound() {
        $this->lib->noeudsRoutierCache = [
            15 => [
                100 => [
                    'unPeuLongueCetteSaeNon?' => 1
                ]
            ],
            5 => [
                999 => [
                    'unPeuLongueCetteSaeNon?' => 12
                ]
            ]
        ];
        $this->lib->numDepartementCourant = 5;
        $this->assertEquals(15, $this->lib->getNumDepartement(100));
    }

    public function testGetNumDepartementFoundDepCourant() {
        $this->lib->noeudsRoutierCache = [
            15 => [
                100 => [
                    'lesAjoutsDeConsigneAuDernierMoment=<3' => 3
                ]
            ],
            5 => [
                999 => [
                    'lesAjoutsDeConsigneAuDernierMoment=<3' => 4
                ]
            ]
        ];
        $this->lib->numDepartementCourant = 5;
        $this->assertEquals(5, $this->lib->getNumDepartement(999));
    }

    public function testReconstruireCheminEmpty() {
        $this->assertEquals([0, [], 0], $this->lib->reconstruireChemin([], 0, [], [], []));
    }

    public function testReconstruireChemin() {
        $cameFrom[1] = 2;
        $cameFrom[2] = 3;
        $cameFrom[3] = 4;
        $cost[1] = 1;
        $cost[2] = 0.5;
        $cost[3] = 3;
        $cost[4] = 1.5;
        $vitesse[1] = 5;
        $vitesse[2] = 10;
        $vitesse[3] = 20;
        $vitesse[4] = 30;
        $coordTrocon[1] = "L";
        $coordTrocon[2] = "O";
        $coordTrocon[3] = "N";
        $coordTrocon[4] = "G";
        $this->assertEquals(
            [6, ['L', 'O', 'N', 'G'], 0.45],
            $this->lib->reconstruireChemin($cameFrom, 1, $cost, $coordTrocon, $vitesse)
        );
    }

    public function testHeuristiqueHaversine() {
        $this->assertEquals(0, $this->lib->getHeuristiqueHaversine(0, 0, 0, 0));
        $this->assertEquals(0, $this->lib->getHeuristiqueHaversine(1, 1, 1, 1));
        $this->assertEquals(157.24938127194397, $this->lib->getHeuristiqueHaversine(1, 1, 0, 0));
    }

    public function testAStarSameDepartment() {
        $noeudRoutierDepart = new NoeudRoutier(61526, 43.59917864959453, 43.56752258324011); // Montpellier
        $noeudRoutierArrivee = new NoeudRoutier(57806, 3.894125217456986, 3.901286019097689); // Lattes
        $noeudsRoutier = [$noeudRoutierDepart, $noeudRoutierArrivee];
        $noeudsRoutierDepartement = json_decode(file_get_contents('..\..\ressources\data\34.json'), true);
        $this->noeudRoutierService->expects($this->once())
            ->method('getNoeudsRoutierDepartement')
            ->willReturn($noeudsRoutierDepartement);
        $result = $this->lib->aStarDistance($noeudsRoutier);
        $this->assertEquals(4.08, round($result[0], 6));
        $this->assertCount(25, $result[1]);
        $path = [
            1225419,
            1225408,
            1225390,
            1225378,
            1225379,
            1225386,
            1225384,
            1225393,
            1225375,
            1223083,
            1223085,
            1223079,
            1223087,
            204490,
            1223088,
            1223089,
            1223104,
            204705,
            156485,
            204706,
            204741,
            204742,
            204745,
            204744,
            204754
        ];
        $this->assertEquals($path, $result[1]);
    }

    public function testAStarDifferentDepartment() {
        $noeudRoutierDepart = new NoeudRoutier(56310, 43.561939791703864, 4.084459714500611); // La Grande Motte
        $noeudRoutierArrivee = new NoeudRoutier(54720, 43.53384787008224, 4.138118267057888); // Grau du Roi
        $noeudsRoutier = [$noeudRoutierDepart, $noeudRoutierArrivee];
        $noeudsRoutierDepartementFirstCall = json_decode(file_get_contents('..\..\ressources\data\34.json'), true);
        $noeudsRoutierDepartementSecondCall = json_decode(file_get_contents('..\..\ressources\data\30.json'), true);
        $this->noeudRoutierService->expects($this->exactly(2))
            ->method('getNoeudsRoutierDepartement')
            ->willReturnOnConsecutiveCalls($noeudsRoutierDepartementFirstCall, $noeudsRoutierDepartementSecondCall);
        $result = $this->lib->aStarDistance($noeudsRoutier);
        $this->assertEquals(6.44, round($result[0], 6));
        $this->assertCount(17, $result[1]);
        $path = [
            1229440,
            130885,
            1229441,
            132775,
            183365,
            183370,
            40486,
            40484,
            40485,
            40483,
            1227359,
            1227358,
            1227360,
            1227373,
            1225304,
            1225296,
            1227372
        ];
        $this->assertEquals($path, $result[1]);
    }

    public function testAStarSameDepartmentOneStep() {
        $noeudRoutierDepart = new NoeudRoutier(903713, 43.548571275422475, 3.977081144893388); // Carnon
        $noeudRoutierStep = new NoeudRoutier(56310, 43.561939791703864, 4.084459714500611); // La Grande Motte
        $noeudRoutierArrivee = new NoeudRoutier(54720, 43.53384787008224, 4.138118267057888); // Grau du Roi
        $noeudsRoutier = [$noeudRoutierDepart, $noeudRoutierStep, $noeudRoutierArrivee];
        $noeudsRoutierDepartementFirstCall = json_decode(file_get_contents('..\..\ressources\data\34.json'), true);
        $noeudsRoutierDepartementSecondCall = json_decode(file_get_contents('..\..\ressources\data\30.json'), true);
        $this->noeudRoutierService->expects($this->exactly(2))
            ->method('getNoeudsRoutierDepartement')
            ->willReturnOnConsecutiveCalls($noeudsRoutierDepartementFirstCall, $noeudsRoutierDepartementSecondCall);
        $result = $this->lib->aStarDistance($noeudsRoutier);
        $this->assertEquals(15.99, round($result[0], 6));
        $this->assertCount(42, $result[1]);
        $path = [
            1227366,
            1227371,
            1237165,
            1227377,
            1237390,
            1227390,
            1236973,
            1227375,
            1227374,
            40826,
            40827,
            40825,
            100392,
            27413,
            27414,
            27406,
            27416,
            27415,
            27407,
            27408,
            27402,
            140248,
            98275,
            1229459,
            40467,
            1229440,
            130885,
            1229441,
            132775,
            183365,
            183370,
            40486,
            40484,
            40485,
            40483,
            1227359,
            1227358,
            1227360,
            1227373,
            1225304,
            1225296,
            1227372
        ];
        $this->assertEquals($path, $result[1]);
    }

    public function testAStarImpossible() {
        $noeudRoutierDepart = new NoeudRoutier(2584, 42.31130862288786, 9.150005433988802); // Corte
        $noeudRoutierArrivee = new NoeudRoutier(61526, 43.59917864959453, 3.894125217456986); // Montpellier
        $noeudsRoutier = [$noeudRoutierDepart, $noeudRoutierArrivee];
        $noeudsRoutierDepartementFirstCall = json_decode(file_get_contents('..\..\ressources\data\2B.json'), true);
        $noeudsRoutierDepartementSecondCall = json_decode(file_get_contents('..\..\ressources\data\2A.json'), true);
        $this->noeudRoutierService->expects($this->atLeast(1))
            ->method('getNoeudsRoutierDepartement')
            ->willReturnOnConsecutiveCalls($noeudsRoutierDepartementFirstCall, $noeudsRoutierDepartementSecondCall);
        $this->expectException(ServiceException::class);
        $this->lib->aStarDistance($noeudsRoutier);
    }

}