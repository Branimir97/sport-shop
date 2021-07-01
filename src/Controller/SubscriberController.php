<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/subscribers",
 *     "hr": "/pretplatnici"
 * })
 */
class SubscriberController extends AbstractController
{
    /**
     * @Route("/", name="subscriber_index", methods={"GET"})
     */
    public function index(SubscriberRepository $subscriberRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('subscriber/index.html.twig', [
            'subscribers' => $subscriberRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/novi"
     * }, name="subscriber_new", methods={"POST"})
     */
    public function new(Request $request,
                        SubscriberRepository $subscriberRepository,
                        UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $subscriber = new Subscriber();
        $email = $request->request->get('email');
        $users = $userRepository->findAll();
        $userEmails = [];
        foreach($users as $user) {
            array_push($userEmails, $user->getEmail());
        }
        if(!is_null($subscriberRepository->findOneBy(['email'=>$email]))) {
            $this->addFlash('danger',
                $translator->trans('flash_message.subscriber_exists',
                    [], 'subscriber'));
            return $this->redirectToRoute('home');
        } else if(in_array($email, $userEmails)) {
            $this->addFlash('danger',
                $translator->trans('flash_message.registered_email_used',
                    [], 'subscriber'));
            return $this->redirectToRoute('home');
        }
        else {
            $subscriber->setEmail($email);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subscriber);
            $entityManager->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.subscriber_added',
                    [], 'subscriber'));
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route({
     *     "en": "/new/registered",
     *     "hr": "/novi/registrirani"
     * }, name="subscriber_new_registered", methods={"GET","POST"})
     */
    public function newRegistered(Request $request,
                                  SubscriberRepository $subscriberRepository,
                                  TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $formEmail = $request->request->get('email');
        $subscriber = new Subscriber();
        $currentEmail = $this->getUser()->getUsername();
        if(!is_null($subscriberRepository->findOneBy(
            ['email'=>$currentEmail])) || ($formEmail !== $currentEmail)) {
            $this->addFlash('danger',
                $translator->trans('flash_message.registered_subscribe_message',
                    [], 'subscriber'));
            return $this->redirectToRoute('account_settings');
        }
        $subscriber->setEmail($currentEmail);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscriber);
        $entityManager->flush();

        $this->addFlash('success',
            $translator->trans('flash_message.subscriber_added',
                [], 'subscriber'));
        return $this->redirectToRoute('account_settings');
    }

    /**
     * @Route({
     *     "en": "/delete/registered",
     *     "hr": "/obriši/registrirani"
     * }, name="subscriber_delete_registered", methods={"GET", "POST"})
     */
    public function deleteRegistered(SubscriberRepository $subscriberRepository,
                                     TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $subscriber = $subscriberRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($subscriber);
        $entityManager->flush();

        $this->addFlash('danger',
            $translator->trans('flash_message.subscriber_deleted',
                [], 'subscriber'));
        return $this->redirectToRoute('account_settings');
    }


    /**
     * @Route("/{id}", name="subscriber_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Subscriber $subscriber,
                           TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        if ($this->isCsrfTokenValid('delete'.$subscriber->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subscriber);
            $entityManager->flush();
        }

        $this->addFlash('danger',
            $translator->trans('flash_message.subscriber_deleted',
                [], 'subscriber'));
        return $this->redirectToRoute('subscriber_index');
    }
}
