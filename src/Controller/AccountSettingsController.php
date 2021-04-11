<?php

namespace App\Controller;

use App\Form\EditProfileFormType;
use App\Repository\DeliveryAddressRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountSettingsController extends AbstractController
{
    /**
     * @Route("/account/settings", name="account_settings")
     * @param DeliveryAddressRepository $deliveryAddressRepository
     * @return Response
     */
    public function index(DeliveryAddressRepository $deliveryAddressRepository): Response
    {
        $deliveryAddresses = $deliveryAddressRepository->findBy(['user'=>$this->getUser()]);
        return $this->render('account_settings/details.html.twig', [
            'deliveryAddresses'=>$deliveryAddresses
        ]);
    }
}
