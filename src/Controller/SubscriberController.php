<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Form\SubscriberType;
use App\Repository\SubscriberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/subscriber")
 */
class SubscriberController extends AbstractController
{
    /**
     * @Route("/", name="subscriber_index", methods={"GET"})
     */
    public function index(SubscriberRepository $subscriberRepository): Response
    {
        return $this->render('subscriber/index.html.twig', [
            'subscribers' => $subscriberRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="subscriber_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $subscriber = new Subscriber();
        $form = $this->createForm(SubscriberType::class, $subscriber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subscriber);
            $entityManager->flush();

            return $this->redirectToRoute('subscriber_index');
        }

        return $this->render('subscriber/new.html.twig', [
            'subscriber' => $subscriber,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new/registered", name="subscriber_new_registered", methods={"GET","POST"})
     */
    public function newRegistered(Request $request): Response
    {
        $subscriber = new Subscriber();
        $email = $this->getUser()->getUsername();
        $subscriber->setEmail($email);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscriber);
        $entityManager->flush();

        $this->addFlash('success', 'Uspješno ste se pretplatili na naš newsletter.');
        return $this->redirectToRoute('account_settings');
    }

    /**
     * @Route("/delete/registered", name="subscriber_delete_registered", methods={"GET", "POST"})
     */
    public function deleteRegistered(SubscriberRepository $subscriberRepository): Response
    {
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
        if ($this->isCsrfTokenValid('delete'.$subscriber->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subscriber);
            $entityManager->flush();
        }

        $this->addFlash('danger', 'Pretplata uspješno uklonjena.');
        return $this->redirectToRoute('subscriber_index');
    }
}
