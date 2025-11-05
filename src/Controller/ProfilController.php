<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\ProfilType;
use App\Repository\TrajetRepository;
use App\Entity\Trajet;
use App\Form\TrajetType;
use App\Repository\ReservationRepository;
use App\Entity\Reservation;
use App\Form\ReservationType;

final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/modifier', name: 'app_profil_modifier')]
    public function modifier(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();
            
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/modifier.html.twig', [
            'user' => $user,
            'profil' => $form,
        ]);
    }

    #[Route('/profil/trajets', name: 'app_profil_trajets')]
    public function trajets(TrajetRepository $trajetRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        $trajets = $trajetRepository->findBy(['utilisateur' => $user], ['date_et_heure' => 'ASC']);

        return $this->render('profil/trajets.html.twig', [
            'user' => $user,
            'trajets' => $trajets,
        ]);
    }

    #[Route('/profil/trajet/{id}/modifier', name: 'app_profil_trajet_modifier')]
    public function modifierTrajet(Trajet $trajet, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($trajet->getUtilisateur() !== $this->getUser() || $trajet->getDateEtHeure() < new \DateTime()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce trajet.');
        }

        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            return $this->redirectToRoute('app_profil_trajets');
        }

        return $this->render('profil/trajet_modifier.html.twig', [
            'trajet' => $trajet,
            'form' => $form,
        ]);
    }

    #[Route('/profil/trajet/{id}/supprimer', name: 'app_profil_trajet_supprimer')]
    public function supprimerTrajet(Trajet $trajet, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        if ($trajet->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce trajet.');
        }

        $entityManager->remove($trajet);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_profil_trajets');
    }

    #[Route('/profil/reservations', name: 'app_profil_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        $reservations = $reservationRepository->createQueryBuilder('r')
            ->join('r.trajet', 't')
            ->where('r.utilisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('t.date_et_heure', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('profil/reservations.html.twig', [
            'user' => $user,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/profil/reservation/{id}/modifier', name: 'app_profil_reservation_modifier')]
    public function modifierReservation(Reservation $reservation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser() || $reservation->getTrajet()->getDateEtHeure() < new \DateTime()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette réservation.');
        }

        $trajet = $reservation->getTrajet();

        $siegesReserves = $reservation->getSiegesReserves();

        $trajet->setSiegesLibres($trajet->getSiegesLibres() + $siegesReserves);

        $form = $this->createForm(ReservationType::class, $reservation, [
            'trajet' => $trajet
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le nouveau nombre de sièges du formulaire
            $nouveauNombreSieges = $form->get('sieges_reserves')->getData();
            
            // Remettre les sièges libres à leur état original
            $trajet->setSiegesLibres($trajet->getSiegesLibres() - $siegesReserves);
            
            // Calculer et appliquer la nouvelle allocation
            $trajet->setSiegesLibres($trajet->getSiegesLibres() - $nouveauNombreSieges);
            
            // Mettre à jour la réservation
            $reservation->setSiegesReserves($nouveauNombreSieges);
            
            $entityManager->flush();

            return $this->redirectToRoute('app_profil_reservations');
        } else {
            // Si le formulaire n'est pas soumis, remettre les sièges dans leur état original
            $trajet->setSiegesLibres($trajet->getSiegesLibres() - $siegesReserves);
        }

        return $this->render('profil/reservation_modifier.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/profil/reservation/{id}/supprimer', name: 'app_profil_reservation_supprimer')]
    public function supprimerReservation(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette réservation.');
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        return $this->redirectToRoute('app_profil_reservations');
    }
}
