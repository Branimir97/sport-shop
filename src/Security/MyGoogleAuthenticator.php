<?php


namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MyGoogleAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $router;

    public function __construct(ClientRegistry $clientRegistry,
                                EntityManagerInterface $em,
                                RouterInterface $router) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
    }

    public function start(Request $request,
                          AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse(
            '/connect/',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function getCredentials(Request $request): AccessToken
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);
        $email = $googleUser->getEmail();
        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['googleId' => $googleUser->getId()]);
        if ($existingUser) {
            return $existingUser;
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if(!$user) {
            $user = new User();
            $user->setGoogleId($googleUser->getId());
            $user->setName($googleUser->getFirstName());
            $user->setSurname($googleUser->getLastName());
            $user->setEmail($googleUser->getEmail());
            $this->em->persist($user);
            $this->em->flush();
        }
        return $user;
    }

    private function getGoogleClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('google');
    }

    public function onAuthenticationFailure(Request $request,
                                            AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request,
                                            TokenInterface $token,
                                            string $providerKey): ?Response
    {
        $targetUrl = $this->router->generate('homepage');
        return new RedirectResponse($targetUrl);
    }
}