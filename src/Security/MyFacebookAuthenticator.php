<?php


namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MyFacebookAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $router;
    private $mailer;
    private $translator;

    public function __construct(ClientRegistry $clientRegistry,
                                EntityManagerInterface $em,
                                RouterInterface $router,
                                MailerInterface $mailer,
                                TranslatorInterface $translator) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->translator = $translator;
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
        return $request->attributes->get('_route') === 'connect_facebook_check';
    }

    public function getCredentials(Request $request): AccessToken
    {
        return $this->fetchAccessToken($this->getFacebookClient());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var FacebookUser $facebookUser */
        $facebookUser = $this->getFacebookClient()
            ->fetchUserFromToken($credentials);

        $email = $facebookUser->getEmail();

        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['facebookId' => $facebookUser->getId()]);
        if ($existingUser) {
            return $existingUser;
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if(!$user) {
            $user = new User();
            $user->setFacebookId($facebookUser->getId());
            $user->setName($facebookUser->getFirstName());
            $user->setSurname($facebookUser->getLastName());
            $user->setEmail($facebookUser->getEmail());
            $this->em->persist($user);
            $this->em->flush();
        }

        $subject = $this->translator->trans('new_user.subject',
            [], 'email');
        $receiverEmail = $facebookUser->getEmail();
        $email = (new TemplatedEmail())
            ->from('sport-shop@gmail.com')
            ->to($receiverEmail)
            ->subject($subject)
            ->context([
                    'name' => $facebookUser->getFirstName().' '.$facebookUser->getLastName()
                ]
            )
            ->htmlTemplate('email/new_user.html.twig');
        $this->mailer->send($email);

        return $user;
    }
    private function getFacebookClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry
            ->getClient('facebook_main');
    }

    public function onAuthenticationFailure(Request $request,
                                            AuthenticationException $exception): Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request,
                                            TokenInterface $token,
                                            string $providerKey): RedirectResponse
    {
        $targetUrl = $this->router->generate('homepage');
        return new RedirectResponse($targetUrl);
    }
}