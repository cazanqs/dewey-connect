<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ConnexionController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash('information', 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('app_accueil');
        }

        $erreur = $authenticationUtils->getLastAuthenticationError();

        if ($erreur) {
            $this->addFlash('erreur', 'Identifiants incorrects, veuillez vérifier vos identifiants.');
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('connexion/login.html.twig', [
            'last_username' => $lastUsername,
            'erreur' => $erreur,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
