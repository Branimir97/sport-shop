<?php

namespace App\Controller;

use App\Entity\DeliveryAddress;
use App\Form\DeliveryAddressFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeliveryAddressController extends AbstractController
{
    /**
     * @Route("/delivery/address/add", name="delivery_address_new")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $deliveryAddress = new DeliveryAddress();
        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddress);
        $form->handleRequest($request);
        $deliveryAddress->setUser($this->getUser());

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($deliveryAddress);
            $entityManager->flush();
            $this->addFlash('success', 'Adresa isporuke uspješno dodana');
            return $this->redirectToRoute('account_settings');
        }

        return $this->render('delivery_address/index.html.twig', [
            'deliveryAddressForm' => $form->createView()
        ]);
    }
}
