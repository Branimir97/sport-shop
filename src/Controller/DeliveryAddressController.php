<?php

namespace App\Controller;

use App\Entity\DeliveryAddress;
use App\Form\DeliveryAddressType;
use App\Repository\DeliveryAddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/adrese/dostave")
 */
class DeliveryAddressController extends AbstractController
{
    /**
     * @Route("/", name="delivery_address_index", methods={"GET"})
     */
    public function index(DeliveryAddressRepository $deliveryAddressRepository): Response
    {
        return $this->render('delivery_address/index.html.twig', [
            'delivery_addresses' => $deliveryAddressRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/nova", name="delivery_address_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        DeliveryAddressRepository $deliveryAddressRepository): Response
    {
        $deliveryAddress = new DeliveryAddress();
        $form = $this->createForm(DeliveryAddressType::class, $deliveryAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if($deliveryAddressRepository->findOneBy(
                ['user'=>$this->getUser(),'street'=>$formData->getStreet(),
                    'city'=>$formData->getCity(), 'county'=>$formData->getCounty()])) {
                $this->addFlash('danger',
                    'Adresa isporuke već postoji na ovom korisničkom računu.');
                return $this->redirectToRoute('account_settings');
            }
            $deliveryAddress->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($deliveryAddress);
            $entityManager->flush();

            $this->addFlash('success', 'Adresa isporuke uspješno dodana.');
            return $this->redirectToRoute('account_settings');
        }
        return $this->render('delivery_address/new.html.twig', [
            'delivery_address' => $deliveryAddress,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_address_show", methods={"GET"})
     */
    public function show(DeliveryAddress $deliveryAddress): Response
    {
        return $this->render('delivery_address/show.html.twig', [
            'delivery_address' => $deliveryAddress,
        ]);
    }

    /**
     * @Route("/{id}/uredi", name="delivery_address_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DeliveryAddress $deliveryAddress): Response
    {
        $form = $this->createForm(DeliveryAddressType::class, $deliveryAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                'Adresa isporuke "'.$deliveryAddress->getStreet().' '.
                            $deliveryAddress->getPostalCode().' '.
                            $deliveryAddress->getCity().'" uspješno ažurirana.');
            if($this->isGranted("ROLE_ADMIN")){
                return $this->redirectToRoute('delivery_address_index');
            } else {
                return $this->redirectToRoute('account_settings');
            }
        }
        return $this->render('delivery_address/edit.html.twig', [
            'delivery_address' => $deliveryAddress,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_address_delete", methods={"DELETE"})
     */
    public function delete(Request $request, DeliveryAddress $deliveryAddress,
                           DeliveryAddressRepository $deliveryAddressRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$deliveryAddress->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryAddress);

            $deliveryAddress = $deliveryAddressRepository->findOneBy(
                ['id'=>$request->get('id')]);
            $this->addFlash(
                'danger',
                'Adresa isporuke "'.$deliveryAddress->getStreet().' '
                .$deliveryAddress->getPostalCode().' '
                .$deliveryAddress->getCity().'" uspješno obrisana.'
            );
            $entityManager->flush();
        }
        if($this->isGranted("ROLE_ADMIN")){
            return $this->redirectToRoute('delivery_address_index');
        } else {
            return $this->redirectToRoute('account_settings');
        }
    }
}
