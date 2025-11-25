<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Trajet;
use App\Entity\Utilisateur;

class TrajetTest extends KernelTestCase
{
    public function testEntiteValide(): void
    {
        self::bootKernel();

        $conteneur = static::getContainer();

        $trajet = new Trajet();
        $trajet->setPointDeDepart('78 Rue Pierre de Coubertin, 33130 Bègles');
        $trajet->setDestination('Espace de coworking');
        $trajet->setDateEtHeure(new \DateTime('2025-12-17 09:30:00'));
        $trajet->setSiegesLibres(3);

        $entityManager = $conteneur->get('doctrine')->getManager();

        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find(1);

        $trajet->setUtilisateur($utilisateur);

        $erreurs = $conteneur->get('validator')->validate($trajet);

        $this->assertCount(0, $erreurs);
    }

    public function testEntiteInvalide(): void
    {
        self::bootKernel();

        $conteneur = static::getContainer();

        $trajet = new Trajet();
        $trajet->setPointDeDepart('');
        $trajet->setDestination('');
        $trajet->setDateEtHeure(new \DateTime('2025-01-01 00:00:00'));
        $trajet->setSiegesLibres(0);
        $trajet->setUtilisateur(null);

        $erreurs = $conteneur->get('validator')->validate($trajet);

        $this->assertCount(5, $erreurs);
    }
}
