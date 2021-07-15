<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\SubscriberRepository;
use App\Security\AppAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/register",
     *     "hr": "/registracija"
     *     }, name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param AppAuthenticator $authenticator
     * @param SubscriberRepository $subscriberRepository
     * @param TranslatorInterface $translator
     * @param MailerInterface $mailer
     * @return Response
     */
    public function register(Request $request,
                             UserPasswordEncoderInterface $passwordEncoder,
                             GuardAuthenticatorHandler $guardHandler,
                             AppAuthenticator $authenticator,
                             SubscriberRepository $subscriberRepository,
                             TranslatorInterface $translator,
                             MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword($user, $form->get('password')->getData())
            );
            $entityManager = $this->getDoctrine()->getManager();
            $subscribeMe = $form->get('subscribeMe')->getData();
            if($subscribeMe == true && is_null($subscriberRepository->findOneBy(
                ['email' => $user->getEmail()]))){
                $subscriber = new Subscriber();
                $subscriber->setEmail($user->getEmail());
                $entityManager->persist($subscriber);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            $subject = $translator->trans('new_user.subject',
                [], 'email');
            $receiverEmail = $user->getEmail();
            $email = (new TemplatedEmail())
                ->to($receiverEmail)
                ->subject($subject)
                ->context([
                        'name' => $user->getFullName()
                    ]
                )
                ->htmlTemplate('email/new_user.html.twig');
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $exception) {}

            if($this->isGranted("ROLE_ADMIN")){
                $this->addFlash('success',
                    $translator->trans('flash_message.registered_admin',
                        [], 'register'));
                return $this->redirectToRoute('user_index');
            } else {
                $this->addFlash('success',
                    $translator->trans('flash_message.registered_user',
                        [], 'register'));
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user, $request, $authenticator, 'main');
            }
        }
        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
