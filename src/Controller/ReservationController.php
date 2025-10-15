<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Trajet;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservation;

final class ReservationController extends AbstractController
{
    #[Route('/reservation/{id}', name: 'app_reservation', methods: ['GET', 'POST'])]
    public function index(Trajet $trajet, Request $request, EntityManagerInterface $entityManager): Response
    {
        $siegesReserves = (int) $request->request->get('sieges_reserves');
        
        if ($siegesReserves > 0 && $siegesReserves <= $trajet->getSiegesLibres()) {
            $reservation = new Reservation();
            $reservation->setUtilisateur($this->getUser());
            $reservation->setTrajet($trajet);
            $reservation->setSiegesReserves($siegesReserves);
            
            $trajet->setSiegesLibres($trajet->getSiegesLibres() - $siegesReserves);
            
            $entityManager->persist($reservation);
            $entityManager->flush();
            
            $this->addFlash('success', 'Réservation effectuée avec succès !');
        }
        
        return $this->redirectToRoute('app_accueil');
    }
}
