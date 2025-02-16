<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_task');
        }

        // Récupérer l'erreur d'authentification s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupérer le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Rendu de la vue avec les données
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode est laissée vide, elle est interceptée par Symfony pour effectuer la déconnexion
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

