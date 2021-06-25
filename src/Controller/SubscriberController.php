<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            'subscribers' => $subscriberRepository->findBy([], ['id'=>'DESC']),
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
                        UserRepository $userRepository): Response
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
                'Već postoji pretplaćeni korisnik na navedenoj email adresi.');
            return $this->redirectToRoute('home');
        } else if(in_array($email, $userEmails)) {
            $this->addFlash('danger',
                'Već postoji registrirani korisnik na navedenoj email adresi.');
            return $this->redirectToRoute('home');
        }
        else {
            $subscriber->setEmail($email);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subscriber);
            $entityManager->flush();

            $this->addFlash('success',
                'Uspješno ste se pretplatili na naš newsletter!');
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
                                  SubscriberRepository $subscriberRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $formEmail = $request->request->get('email');
        $subscriber = new Subscriber();
        $currentEmail = $this->getUser()->getUsername();
        if(!is_null($subscriberRepository->findOneBy(
            ['email'=>$currentEmail])) || ($formEmail !== $currentEmail)) {
            $this->addFlash('danger',
                'Ukoliko niste pretplaćeni, možete se pretplatiti isključivo s 
                        Vašom email adresom korištenom za registraciju ovog računa.');
            return $this->redirectToRoute('account_settings');
        }
        $subscriber->setEmail($currentEmail);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscriber);
        $entityManager->flush();

        $this->addFlash('success',
            'Uspješno ste se pretplatili na naš newsletter.');
        return $this->redirectToRoute('account_settings');
    }

    /**
     * @Route({
     *     "en": "/delete/registered",
     *     "hr": "/obriši/registrirani"
     * }, name="subscriber_delete_registered", methods={"GET", "POST"})
     */
    public function deleteRegistered(SubscriberRepository $subscriberRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $subscriber = $subscriberRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($subscriber);
        $entityManager->flush();

        $this->addFlash('danger', 'Pretplata uspješno uklonjena.');
        return $this->redirectToRoute('account_settings');
    }


    /**
     * @Route("/{id}", name="subscriber_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Subscriber $subscriber): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        if ($this->isCsrfTokenValid('delete'.$subscriber->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subscriber);
            $entityManager->flush();
        }

        $this->addFlash('danger', 'Pretplata uspješno uklonjena.');
        return $this->redirectToRoute('subscriber_index');
    }
}
