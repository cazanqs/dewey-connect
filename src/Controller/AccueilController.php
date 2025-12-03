<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TrajetRepository;
use App\Entity\Trajet;
use App\Form\TrajetType;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, EntityManagerInterface $entityManager, TrajetRepository $trajetRepository): Response
    {
        $trajet = new Trajet();

        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->getUser()) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour créer un trajet.');

                return $this->redirectToRoute('app_login');
            }

            $trajet->setUtilisateur($this->getUser());

            if ($form->isValid()) {
                $date = $trajet->getDateEtHeure();

                $siegesLibres = $trajet->getSiegesLibres();

                try {
                    $entityManager->persist($trajet);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre trajet a été créé avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('erreur', 'Une erreur est survenue lors de la création du trajet, veuillez réessayer.');
                }

                return $this->redirectToRoute('app_accueil');
            }
        }

        $maintenant = new \DateTimeImmutable();
        
        $trajets = $trajetRepository->findFutursTrajetsLibres($maintenant);

        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'form' => $form->createView(),
            'trajets' => $trajets,
        ]);
    }
}
