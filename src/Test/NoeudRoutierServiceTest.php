<?php

namespace Navigator\Test;

use Navigator\Modele\Repository\NoeudCommuneRepositoryInterface;
use Navigator\Modele\Repository\NoeudRoutierRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudRoutierService;
use PHPUnit\Framework\TestCase;

class NoeudRoutierServiceTest extends TestCase
{

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
        $result = $this->service->getNoeudRoutierProche(1, 1);
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

    /**
     * @throws ServiceException
     */
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

    /**
     * @throws ServiceException
     */
    public function testGetNomCommunesEmpty() {
        $this->noeudCommuneRepositoryMock->method('getNomCommunes')->willReturn([]);
        $this->service->getNomCommunes("PellierMont");
        $this->assertEquals([], $this->service->getNomCommunes("PellierMont"));
    }

    /**
     * @throws ServiceException
     */
    public function testGetCoordNoeudByGid() {
        $this->noeudRoutierRepositoryMock->method('getCoordNoeudByGid')->willReturn([
            "lat" => "43.59917864959453",
            "long" => "3.894125217456986"
        ]);

        $result = $this->service->getCoordNoeudByGid(1);
        $this->assertEquals([
            "lat" => "43.59917864959453",
            "long" => "3.894125217456986"
        ], $result);
    }

    public function testGetCoordNoeudByGidException() {
        $this->noeudRoutierRepositoryMock->method('getCoordNoeudByGid')->willReturn(null);
        $this->expectException(ServiceException::class);
        $this->service->getCoordNoeudByGid(1);
    }

    /**
     * @throws ServiceException
     */
    public function testGetNoeudsRoutierDepartement() {

        $noeudsRoutierDepartement = json_decode(file_get_contents('C:\xampp\htdocs\NaviGator\ressources\data\2A.json'), true);
        $this->noeudRoutierRepositoryMock->method('getNoeudsRoutierDepartement')->willReturn($noeudsRoutierDepartement);

        $result = $this->service->getNoeudsRoutierDepartement(1);
        $this->assertIsArray($result, "Le résultat doit être un tableau");
        $this->assertNotEmpty($result, "Le tableau ne doit pas être vide");
        $this->assertEquals($noeudsRoutierDepartement, $result);
    }

    public function testGetNoeudsRoutierDepartementException() {
        $this->noeudRoutierRepositoryMock->method('getNoeudsRoutierDepartement')->willReturn([]);
        $this->expectException(ServiceException::class);
        $this->service->getNoeudsRoutierDepartement(1746867584);
    }

}