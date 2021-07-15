<?php

namespace App\Controller;

use App\Entity\DeliveryAddress;
use App\Form\DeliveryAddressType;
use App\Repository\DeliveryAddressRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/delivery/addresses",
 *     "hr": "/adrese/isporuka"
 * })
 * @IsGranted("ROLE_USER")
 */
class DeliveryAddressController extends AbstractController
{
    /**
     * @Route("/", name="delivery_address_index", methods={"GET"})
     */
    public function index(DeliveryAddressRepository $deliveryAddressRepository): Response
    {
        return $this->render('delivery_address/index.html.twig', [
            'delivery_addresses' => $deliveryAddressRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="delivery_address_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        DeliveryAddressRepository $deliveryAddressRepository,
                        TranslatorInterface $translator): Response
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
                    $translator->trans('flash_message.delivery_address_exists',
                        [], 'delivery_address'));
                return $this->redirectToRoute('delivery_address_new');
            }
            $deliveryAddress->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($deliveryAddress);
            $entityManager->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.delivery_address_added',
                    [], 'delivery_address'));
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
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="delivery_address_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DeliveryAddress $deliveryAddress,
                         TranslatorInterface $translator): Response
    {
        $form = $this->createForm(DeliveryAddressType::class, $deliveryAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.delivery_address_edited',
                    [
                        '%street%' => $deliveryAddress->getStreet(),
                        '%postal_code%' => $deliveryAddress->getPostalCode(),
                        '%city%' => $deliveryAddress->getCity()
                    ], 'delivery_address'));
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
                           DeliveryAddressRepository $deliveryAddressRepository,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$deliveryAddress->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryAddress);

            $deliveryAddress = $deliveryAddressRepository->findOneBy(
                ['id'=>$request->get('id')]);
            $this->addFlash('danger',
                $translator->trans('flash_message.delivery_address_deleted',
                    [
                        '%street%' => $deliveryAddress->getStreet(),
                        '%postal_code%' => $deliveryAddress->getPostalCode(),
                        '%city%' => $deliveryAddress->getCity()
                    ], 'delivery_address'));
            $entityManager->flush();
        }
        if($this->isGranted("ROLE_ADMIN")){
            return $this->redirectToRoute('delivery_address_index');
        } else {
            return $this->redirectToRoute('account_settings');
        }
    }
}
