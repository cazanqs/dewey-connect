<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Trajet;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservation;
use App\Form\ReservationType;

final class ReservationController extends AbstractController
{
    #[Route('/reservation/{id}', name: 'app_reservation')]
    public function index(Trajet $trajet, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $reservation->setTrajet($trajet);
        $reservation->setUtilisateur($this->getUser());
        
        $form = $this->createForm(ReservationType::class, $reservation, [
            'trajet' => $trajet
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $siegesReserves = $reservation->getSiegesReserves();
            
            if ($siegesReserves > 0 && $siegesReserves <= $trajet->getSiegesLibres()) {
                $trajet->setSiegesLibres($trajet->getSiegesLibres() - $siegesReserves);
                
                $entityManager->persist($reservation);
                $entityManager->flush();
                
                return $this->redirectToRoute('app_accueil');
            }
        }

        return $this->render('reservation/index.html.twig', [
            'trajet' => $trajet,
            'form' => $form->createView(),
        ]);
    }
}
