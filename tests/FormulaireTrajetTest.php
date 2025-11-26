<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Utilisateur;

class FormulaireTrajetTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find(1);

        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Créer un trajet');

        $bouton = $crawler->selectButton('Créer le trajet');

        $formulaire = $bouton->form();

        $formulaire['trajet[point_de_depart]'] = 'Espace de coworking';
        $formulaire['trajet[destination]'] = '57 Rue du Docteur Albert Barraud, 33000 Bordeaux';
        $formulaire['trajet[date_et_heure]'] = '2025-12-15T18:00';
        $formulaire['trajet[sieges_libres]'] = 3;

        $client->submit($formulaire);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains(
            '.message',
            'Votre trajet a été créé avec succès !'
        );
    }
}
