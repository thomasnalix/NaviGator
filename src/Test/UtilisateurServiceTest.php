<?php

namespace Navigator\Test;

use Navigator\Lib\ConnexionUtilisateurInterface;
use Navigator\Modele\Repository\UtilisateurRepositoryInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\UtilisateurService;
use PHPUnit\Framework\TestCase;

class UtilisateurServiceTest extends TestCase {

    private $service;
    private $connexionUtilisateurMock;
    private $utilisateurRepositoryMock;


    protected function setUp(): void {
        parent::setUp();
        $this->connexionUtilisateurMock = $this->createMock(ConnexionUtilisateurInterface::class);
        $this->utilisateurRepositoryMock = $this->createMock(UtilisateurRepositoryInterface::class);
        $this->service = new UtilisateurService($this->connexionUtilisateurMock, $this->utilisateurRepositoryMock);
    }


    public function testCreateUserExceptionMissingLogin() {
        $this->expectException(ServiceException::class);
        $this->service->creerUtilisateur(null, 'nom', 'prenom', 'motDePasse', 'motDePasse2', 'marqueVehicule', 'modeleVehicule');
    }

    public function testCreateUserExceptionMissingName() {
        $this->expectException(ServiceException::class);
        $this->service->creerUtilisateur('login', null, 'prenom', 'motDePasse', 'motDePasse2', 'marqueVehicule', 'modeleVehicule');
    }

    public function testCreateUserExceptionPasswordMismatch() {
        $this->expectException(ServiceException::class);
        $this->service->creerUtilisateur('login', 'nom', 'prenom', 'motDePasse', 'motDePasse2', 'marqueVehicule', 'modeleVehicule');
    }

    public function testAddUserException() {
        $this->expectException(ServiceException::class);
        $this->utilisateurRepositoryMock->method('ajouter')->willReturn(false);
        $this->service->creerUtilisateur('login', 'nom', 'prenom', 'motDePasse', 'motDePasse', 'marqueVehicule', 'modeleVehicule');
    }

    public function testDeleteUserMissingLogin() {
        $this->expectException(ServiceException::class);
        $this->service->supprimerUtilisateur(null);
    }

    public function testDeleteUserUnknownLogin() {
        $this->expectException(ServiceException::class);
        $this->utilisateurRepositoryMock->method('supprimer')->willReturn(false);
        $this->service->supprimerUtilisateur('login');
    }

    public function testUpdateUserExceptionMissingLogin() {
        $this->expectException(ServiceException::class);
        $this->service->mettreAJourUtilisateur(null, 'nom', 'prenom', 'motDePasseAncien', 'motDePasse', 'motDePasse2', 'marqueVehicule', 'modeleVehicule');
    }

    public function testUpdateUserExceptionMissingName() {
        $this->expectException(ServiceException::class);
        $this->service->mettreAJourUtilisateur('login', null, 'prenom', 'motDePasseAncien', 'motDePasse', 'motDePasse2', 'marqueVehicule', 'modeleVehicule');
    }

    public function testUpdateUserExceptionPasswordMismatch() {
        $this->expectException(ServiceException::class);
        $this->service->mettreAJourUtilisateur('login', 'nom', 'prenom', 'motDePasseAncien', 'motDePasse', 'motDePasse2', 'marqueVehicule', 'modeleVehicule');
    }

    public function testUpdateUserExceptionDisconnected() {
        $this->expectException(ServiceException::class);
        $this->connexionUtilisateurMock->method('estConnecte')->willReturn(false);
        $this->service->mettreAJourUtilisateur('login', 'nom', 'prenom', 'motDePasseAncien', 'motDePasse', 'motDePasse', 'marqueVehicule', 'modeleVehicule');
    }

    public function testVerifyIdsUserExceptionMissingLogin() {
        $this->expectException(ServiceException::class);
        $this->service->verifierIdentifiantUtilisateur(null, 'motDePasse');
    }

    public function testVerifyIdsUserExceptionMissingPassword() {
        $this->expectException(ServiceException::class);
        $this->service->verifierIdentifiantUtilisateur('login', null);
    }

    public function testDisplayUserDetailsExceptionMissingLogin() {
        $this->expectException(ServiceException::class);
        $this->service->afficherDetailUtilisateur(null);
    }

    public function testDisplayUpdateFormUserExceptionDisconnected() {
        $this->expectException(ServiceException::class);
        $this->connexionUtilisateurMock->method('estConnecte')->willReturn(false);
        $this->service->afficherFormulaireMAJUtilisateur('login');
    }

    public function testUpdateUserCarExceptionDisconnected() {
        $this->expectException(ServiceException::class);
        $this->connexionUtilisateurMock->method('estConnecte')->willReturn(false);
        $this->service->updateVoiture('login', 'marqueVehicule', 'modeleVehicule');
    }

}