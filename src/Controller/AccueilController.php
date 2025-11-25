<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Trajet;
use App\Form\TrajetType;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trajet = new Trajet();

        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour créer un trajet.');

                return $this->redirectToRoute('app_login');
            }

            $date = $trajet->getDateEtHeure();

            $siegesLibres = $trajet->getSiegesLibres();

            if (!$date || $date <= new \DateTime()) {
                $this->addFlash('erreur', 'Veuillez renseigner une date et une heure qui ne sont pas encore passées.');
            } elseif ($siegesLibres === null || $siegesLibres <= 0) {
                $this->addFlash('erreur', 'Veuillez renseigner au minimum 1 siège libre.');
            } else {
                try {
                    $trajet->setUtilisateur($this->getUser());

                    $entityManager->persist($trajet);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre trajet a été créé avec succès !');

                } catch (\Exception $e) {
                    $this->addFlash('erreur', 'Une erreur est survenue lors de la création du trajet, veuillez réessayer.');
                }

                return $this->redirectToRoute('app_accueil');
            }
        }

        $trajetsRepository = $entityManager->getRepository(Trajet::class);

        $trajets = $trajetsRepository->createQueryBuilder('t')
            ->where('t.date_et_heure > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.date_et_heure', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'form' => $form->createView(),
            'trajets' => $trajets,
        ]);
    }
}
