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
            $trajet->setUtilisateur($this->getUser());

            $entityManager->persist($trajet);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_accueil');
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
