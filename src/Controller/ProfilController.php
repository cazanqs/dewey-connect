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
        try {
            $utilisateur = $this->getUser();

            if (!$utilisateur) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour accéder à votre profil.');
                
                return $this->redirectToRoute('app_login');
            }

            return $this->render('profil/index.html.twig', [
                'utilisateur' => $utilisateur,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors du chargement de votre profil, veuillez réessayer.');
            
            return $this->redirectToRoute('app_accueil');
        }
    }

    #[Route('/profil/modifier', name: 'app_profil_modifier')]
    public function modifier(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();

        if (!$utilisateur) {
            $this->addFlash('erreur', 'Veuillez vous connecter pour modifier votre profil.');
            
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();

                if ($plainPassword) {
                    $utilisateur->setPassword($userPasswordHasher->hashPassword($utilisateur, $plainPassword));
                }

                $entityManager->flush();
                
                $this->addFlash('success', 'Votre profil a été modifié avec succès !');
                
                return $this->redirectToRoute('app_profil');
            } catch (\Exception $e) {
                $this->addFlash('erreur', 'Une erreur est survenue lors de la modification de votre profil, veuillez réessayer.');

                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('profil/modifier.html.twig', [
            'utilisateur' => $utilisateur,
            'profil' => $form,
        ]);
    }

    #[Route('/profil/trajets', name: 'app_profil_trajets')]
    public function trajets(TrajetRepository $trajetRepository): Response
    {
        try {
            $utilisateur = $this->getUser();

            if (!$utilisateur) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour voir vos trajets.');

                return $this->redirectToRoute('app_login');
            }

            $maintenant = new \DateTimeImmutable();
            
            $trajets = $trajetRepository->findByUtilisateurTriesParDate($utilisateur, $maintenant);

            return $this->render('profil/trajets.html.twig', [
                'utilisateur' => $utilisateur,
                'trajets' => $trajets,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors du chargement de vos trajets, veuillez réessayer.');

            return $this->redirectToRoute('app_profil');
        }
    }

    #[Route('/profil/trajet/{id}/modifier', name: 'app_profil_trajet_modifier')]
    public function modifierTrajet(Trajet $trajet, Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $utilisateur = $this->getUser();

            if (!$utilisateur) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour modifier vos trajets.');

                return $this->redirectToRoute('app_login');
            }

            if ($trajet->getUtilisateur() !== $utilisateur) {
                $this->addFlash('erreur', 'Veuillez sélectionner un trajet qui vous appartient.');

                return $this->redirectToRoute('app_profil_trajets');
            }

            if ($trajet->getDateEtHeure() < new \DateTime()) {
                $this->addFlash('erreur', 'Veuillez sélectionner un trajet qui n\'a pas encore eu lieu.');

                return $this->redirectToRoute('app_profil_trajets');
            }

            $form = $this->createForm(TrajetType::class, $trajet);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre trajet a été modifié avec succès !');

                return $this->redirectToRoute('app_profil_trajets');
            }

            return $this->render('profil/trajet_modifier.html.twig', [
                'trajet' => $trajet,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors de la modification du trajet, veuillez réessayer.');

            return $this->redirectToRoute('app_profil_trajets');
        }
    }

    #[Route('/profil/trajet/{id}/supprimer', name: 'app_profil_trajet_supprimer')]
    public function supprimerTrajet(Trajet $trajet, EntityManagerInterface $entityManager): Response
    {
        try {
            $utilisateur = $this->getUser();

            if (!$utilisateur) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour supprimer votre trajet.');

                return $this->redirectToRoute('app_login');
            }

            if ($trajet->getUtilisateur() !== $utilisateur) {
                $this->addFlash('erreur', 'Veuillez sélectionner un trajet qui vous appartient.');

                return $this->redirectToRoute('app_profil_trajets');
            }

            $reservations = $trajet->getReservations();

            if (!$reservations->isEmpty()) {
                $compteur = count($reservations);
                
                foreach ($reservations as $reservation) {
                    $entityManager->remove($reservation);
                }

                $entityManager->remove($trajet);
                $entityManager->flush();
                
                if ($compteur === 1) {
                    $this->addFlash('success', 'Votre trajet et la réservation associée ont été supprimés avec succès !');
                } else {
                    $this->addFlash('success', 'Votre trajet et les réservations associées ont été supprimés avec succès !');
                }

                return $this->redirectToRoute('app_profil_trajets');
            } else {
                $entityManager->remove($trajet);
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre trajet a été supprimé avec succès !');
            }

            return $this->redirectToRoute('app_profil_trajets');
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors de la suppression du trajet, veuillez réessayer.');

            return $this->redirectToRoute('app_profil_trajets');
        }
    }

    #[Route('/profil/reservations', name: 'app_profil_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        try {
            $utilisateur = $this->getUser();

            if (!$utilisateur) {
                $this->addFlash('erreur', 'Veuillez vous connecter pour voir vos réservations.');

                return $this->redirectToRoute('app_login');
            }

            $maintenant = new \DateTimeImmutable();
            
            $reservations = $reservationRepository->findByUtilisateurTriesParDate($utilisateur, $maintenant);

            return $this->render('profil/reservations.html.twig', [
                'utilisateur' => $utilisateur,
                'reservations' => $reservations,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors du chargement de vos réservations, veuillez réessayer.');

            return $this->redirectToRoute('app_profil');
        }
    }

    #[Route('/profil/reservation/{id}/modifier', name: 'app_profil_reservation_modifier')]
    public function modifierReservation(Reservation $reservation, Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($reservation->getUtilisateur() !== $this->getUser()) {
                $this->addFlash('erreur', 'Veuillez sélectionner une réservation qui vous appartient.');

                return $this->redirectToRoute('app_profil_reservations');
            }

            if ($reservation->getTrajet()->getDateEtHeure() < new \DateTime()) {
                $this->addFlash('erreur', 'Veuillez sélectionner une réservation dont le trajet n\'a pas encore eu lieu.');

                return $this->redirectToRoute('app_profil_reservations');
            }

            $trajet = $reservation->getTrajet();
            
            $ancienneReservation = $reservation->getSiegesReserves();

            $siegesLibres = $trajet->getSiegesLibres() + $ancienneReservation;

            $dupplicationTrajet = clone $trajet;
            $dupplicationTrajet->setSiegesLibres($siegesLibres);

            $form = $this->createForm(ReservationType::class, $reservation, [
                'trajet' => $dupplicationTrajet
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $nouvelleReservation = $form->get('sieges_reserves')->getData();

                $difference = $ancienneReservation - $nouvelleReservation;

                $trajet->setSiegesLibres($trajet->getSiegesLibres() + $difference);

                $reservation->setSiegesReserves($nouvelleReservation);

                $entityManager->flush();

                $this->addFlash('success', 'Votre réservation a été modifiée avec succès !');

                return $this->redirectToRoute('app_profil_reservations');
            }

            return $this->render('profil/reservation_modifier.html.twig', [
                'reservation' => $reservation,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors de la modification de votre réservation, veuillez réessayer.');

            return $this->redirectToRoute('app_profil_reservations');
        }
    }

    #[Route('/profil/reservation/{id}/supprimer', name: 'app_profil_reservation_supprimer')]
    public function supprimerReservation(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($reservation->getUtilisateur() !== $this->getUser()) {
                $this->addFlash('erreur', 'Veuillez sélectionner une réservation qui vous appartient.');
                
                return $this->redirectToRoute('app_profil_reservations');
            }

            $trajet = $reservation->getTrajet();

            $siegesALiberer = $reservation->getSiegesReserves();
            
            $trajet->setSiegesLibres($trajet->getSiegesLibres() + $siegesALiberer);

            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réservation a été supprimée avec succès !');

            return $this->redirectToRoute('app_profil_reservations');
        } catch (\Exception $e) {
            $this->addFlash('erreur', 'Une erreur est survenue lors de la suppression de votre réservation, veuillez réessayer.');

            return $this->redirectToRoute('app_profil_reservations');
        }
    }
}
