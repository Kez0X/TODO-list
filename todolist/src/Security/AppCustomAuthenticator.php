<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        // Utilisez request->request->get pour obtenir les paramètres du formulaire
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $csrfToken = $request->request->get('_csrf_token');

        // Enregistrez le dernier nom d'utilisateur dans la session
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Créez et retournez un Passport avec les informations d'authentification
        return new Passport(
            new UserBadge($email), // Identifiant utilisateur (ici l'email)
            new PasswordCredentials($password), // Mot de passe pour l'authentification
            [
                new CsrfTokenBadge('authenticate', $csrfToken), // Vérification du token CSRF
                new RememberMeBadge(), // Badge pour "se souvenir de l'utilisateur"
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Vérifiez si une redirection vers une page de destination est disponible
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Sinon, redirigez vers la page des tâches
        return new RedirectResponse($this->urlGenerator->generate('app_task'));
    }

    protected function getLoginUrl(Request $request): string
    {
        // Retournez l'URL du formulaire de connexion
        return $this->urlGenerator->generate('app_login');
    }
}
