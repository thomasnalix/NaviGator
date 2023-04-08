<?php

namespace Navigator\Test;

use Navigator\Modele\DataObject\NoeudRoutier;
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
                    'unPeuLongueCetteSaeNon?' => 10
                ]
            ],
            5 => [
                999 => [
                    'unPeuLongueCetteSaeNon?' => 10
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
                    'lesAjoutsDeConsigneAuDernierMoment=<3' => 10
                ]
            ],
            5 => [
                999 => [
                    'lesAjoutsDeConsigneAuDernierMoment=<3' => 10
                ]
            ]
        ];
        $this->lib->numDepartementCourant = 5;
        $this->assertEquals(5, $this->lib->getNumDepartement(999));
    }

    public function testReconstruireCheminEmpty() {
        self::assertEquals([0, [], 0], $this->lib->reconstruireChemin([], 0, [], [], []));
    }

    public function testReconstruireChemin() {
        $cameFrom[1] = 2; $cameFrom[2] = 3; $cameFrom[3] = 4;
        $cost[1] = 1; $cost[2] = 0.5; $cost[3] = 3; $cost[4] = 1.5;
        $vitesse[1] = 5; $vitesse[2] = 10; $vitesse[3] = 20; $vitesse[4] = 30;
        $coordTrocon[1] = "L"; $coordTrocon[2] = "O"; $coordTrocon[3] = "N"; $coordTrocon[4] = "G";
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
        $noeudRoutierDepart = new NoeudRoutier(61526, 0, 0); // TODO : MTP 34
        $noeudRoutierArrivee = new NoeudRoutier(57806, 0, 0); // TODO : LATTES 34
        $noeudsRoutier = [$noeudRoutierDepart, $noeudRoutierArrivee];
        $this->noeudRoutierService->expects($this->once())
            ->method('getNoeudsRoutierDepartement')
            ->willReturn($noeudsRoutier);
        $this->assertEquals([], $this->lib->aStarDistance($noeudsRoutier)); // TODO GET RESULT
    }

    public function testAStarDifferentDepartment() {
        $noeudRoutierDepart = new NoeudRoutier(0, 0, 0); // TODO : VILLE PROCHE DE LA FRONTIERE 34/30 (grande motte)
        $noeudRoutierArrivee = new NoeudRoutier(0, 0, 0); // TODO : VILLE PROCHE DE LA FRONTIERE 30/34 (grau du roi)
        $noeudsRoutier = [$noeudRoutierDepart, $noeudRoutierArrivee];
        $noeudsRoutierDepartementFirstCall = []; // TODO : 34
        $noeudsRoutierDepartementSecondCall = []; // TODO : 30
        $this->noeudRoutierService->expects($this->exactly(2))
            ->method('getNoeudsRoutierDepartement')
            ->willReturnOnConsecutiveCalls($noeudsRoutierDepartementFirstCall, $noeudsRoutierDepartementSecondCall);
        $this->lib->aStarDistance($noeudsRoutier); // TODO GET RESULT
    }


}