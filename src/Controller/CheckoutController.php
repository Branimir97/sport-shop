<?php

namespace App\Controller;

use App\Form\CheckoutType;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\DeliveryAddressRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(CartRepository $cartRepository, CartItemRepository $cartItemRepository,
                          DeliveryAddressRepository $deliveryAddressRepository, UserRepository $userRepository): Response
    {
        $cart = $cartRepository->findOneBy(['user'=>$this->getUser()]);
        $cartItems = $cartItemRepository->findBy(['cart'=>$cart]);
        $totalPrice = 0;
        foreach($cart->getCartItems() as $cartItem) {
            $totalPrice+=$cartItem->getItem()->getPrice()*$cartItem->getQuantity();
        }
        $activeUserAddresses = $deliveryAddressRepository->findBy(['user'=>$this->getUser()]);
        $userAddressesWithData = [];
        foreach($activeUserAddresses as $activeUserAddress) {
            $userAddressesWithData[$activeUserAddress->getStreet().', '.
                                    $activeUserAddress->getPostalCode().' '.
                                    $activeUserAddress->getCity().' | '.
                                    $activeUserAddress->getCountry()]
                = $activeUserAddress;
        }
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $loyaltyCard = $user->getLoyaltyCard();
        if(!is_null($loyaltyCard)) {
            $loyaltyCardCredits = $loyaltyCard->getCredits();
        } else {
            $loyaltyCardCredits = [];
        }
        $creditsEarned = floor($totalPrice/10);
        $form = $this->createForm(CheckoutType::class, null, ['activeUserAddresses'=>$userAddressesWithData]);
        return $this->render('checkout/index.html.twig', [
            'cartItems'=>$cartItems,
            'totalPrice'=>$totalPrice,
            'form'=>$form->createView(),
            'loyaltyCardCredits'=>$loyaltyCardCredits,
            'creditsEarned'=>$creditsEarned
        ]);
    }
}
