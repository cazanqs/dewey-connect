<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Trajet;
use App\Entity\Utilisateur;

class EntiteTrajetTest extends KernelTestCase
{
    public function testEntiteValide(): void
    {
        self::bootKernel();

        $conteneur = static::getContainer();

        $trajet = new Trajet();
        $trajet->setPointDeDepart('Espace de coworking');
        $trajet->setDestination('57 Rue du Docteur Albert Barraud, 33000 Bordeaux');
        $trajet->setDateEtHeure(new \DateTime('2025-12-17 09:30:00'));
        $trajet->setSiegesLibres(3);

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

        $erreurs = $conteneur->get('validator')->validate($trajet);

        $this->assertCount(4, $erreurs);
    }
}
