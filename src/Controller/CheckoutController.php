<?php

namespace App\Controller;

use App\Entity\OrderList;
use App\Entity\PromoCodeUser;
use App\Form\CheckoutType;
use App\Form\LoyaltyCreditsCheckoutType;
use App\Form\PromoCodeUserType;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\DeliveryAddressRepository;
use App\Repository\PromoCodeRepository;
use App\Repository\PromoCodeUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(Request $request, CartRepository $cartRepository,
                          CartItemRepository $cartItemRepository,
                          DeliveryAddressRepository $deliveryAddressRepository,
                          PromoCodeRepository $promoCodeRepository,
                            PromoCodeUserRepository $promoCodeUserRepository): Response
    {
        $session = $this->get('session');
        $activeUserAddresses = $deliveryAddressRepository->findBy(['user'=>$this->getUser()]);
        $userAddressesWithData = [];
        foreach($activeUserAddresses as $activeUserAddress) {
            $userAddressesWithData[$activeUserAddress->getStreet().', '.
                                    $activeUserAddress->getPostalCode().' '.
                                    $activeUserAddress->getCity().' | '.
                                    $activeUserAddress->getCountry()]
                = $activeUserAddress;
        }
        $formCheckout = $this->createForm(CheckoutType::class, null, ['activeUserAddresses'=>$userAddressesWithData]);
        $formCheckout->handleRequest($request);
        if($formCheckout->isSubmitted() && $formCheckout->isValid()) {
//            $orderList = new OrderList();
//            $orderList->setUser($this->getUser);

        }




        $cart = $cartRepository->findOneBy(['user'=>$this->getUser()]);
        $cartItems = $cartItemRepository->findBy(['cart'=>$cart]);
        $totalPrice = 0;
        $discount = 0;
        foreach($cart->getCartItems() as $cartItem) {
            $totalPrice+=$cartItem->getItem()->getPrice()*$cartItem->getQuantity();
            if($cartItem->getItem()->getActionItem()) {
                $discount += ($cartItem->getItem()->getActionItem()->getDiscountPercentage()/100)*
                    $cartItem->getQuantity()*$cartItem->getItem()->getPrice();
            }
            $cartItemCategories = $cartItem->getItem()->getItemCategories();
            foreach($cartItemCategories as $cartItemCategory) {
                if($cartItemCategory->getCategory()->getActionCategory()) {
                    $discount+=
                        ($cartItemCategory->getCategory()->getActionCategory()->getDiscountPercentage()/100)*
                        $cartItem->getQuantity()*$cartItem->getItem()->getPrice();
                }
            }
        }
        $loyaltyCard = $this->getUser()->getLoyaltyCard();
        if(!is_null($loyaltyCard)) {
            $loyaltyCardCredits = $loyaltyCard->getCredits();
        } else {
            $loyaltyCardCredits = null;
        }
        $promoCodeUser = new PromoCodeUser();
        $formPromoCode = $this->createForm(PromoCodeUserType::class, null, ['loyaltyCardCredits'=>$loyaltyCardCredits]);
        $formPromoCode->handleRequest($request);
        if ($formPromoCode->isSubmitted() && $formPromoCode->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $promoCodeObj = $promoCodeRepository->findOneBy(['code'=>$formPromoCode->get('code')->getData()]);
            if(is_null($promoCodeObj)) {
                $this->addFlash('danger', 'Unijeli ste promo kod koji ne postoji.');
                return $this->redirectToRoute('checkout');
            } else if(is_null($promoCodeObj) && !is_null($loyaltyCardCredits) && $loyaltyCardCredits>0) {
                $useCredits = $formPromoCode->get('use_credits')->getData();
                if($useCredits) {
                    $loyaltyCard->setCredits(0);
                    $entityManager->persist($loyaltyCard);
                    $entityManager->flush();
                    $session->set('discountWithUsedCredits', $loyaltyCardCredits);
                }
            }
            else {
                $promoCodeUserObj = $promoCodeUserRepository->findOneBy(['promoCode'=>$promoCodeObj, 'user'=>$this->getUser()]);
                if(!is_null($promoCodeUserObj)) {
                    $this->addFlash('danger', 'Već ste iskoristili ovaj promo kod.');
                    return $this->redirectToRoute('checkout');
                } else {
                    $promoCodeUser->setUser($this->getUser());
                    $promoCodeUser->setPromoCode($promoCodeObj);
                    $entityManager->persist($promoCodeUser);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Promo code '.$promoCodeObj->getCode(). ' prihvaćen. 
                    Cijena umanjena za '.$promoCodeObj->getDiscountPercentage(). '%.');
                    $session->set('promoCode', $promoCodeObj->getCode());
                    $session->set('promoCodeDiscount', $promoCodeObj->getDiscountPercentage());
                    $session->set('discountWithUsedPromoCode', $totalPrice*($promoCodeObj->getDiscountPercentage()/100));
                    return $this->redirectToRoute('checkout');
                }
            }
        }
        if($session->get('discountWithUsedCredits') !=null) {
            $discount+=$session->get('discountWithUsedCredits');
        }
        if($session->get('discountWithUsedPromoCode') !=null) {
            $discount+=$session->get('discountWithUsedPromoCode');
        }

        if($totalPrice<$discount) {
            $totalPriceWithDiscount = 0;
        } else {
            $totalPriceWithDiscount = $totalPrice-$discount;
        }
        $creditsEarned = floor($totalPriceWithDiscount/10);

        return $this->render('checkout/index.html.twig', [
            'cartItems'=>$cartItems,
            'formCheckout'=>$formCheckout->createView(),
            'loyaltyCardCredits'=>$loyaltyCardCredits,
            'creditsEarned'=>$creditsEarned,
            'totalPrice'=>$totalPrice,
            'discount'=>$discount,
            'totalPriceWithDiscount'=>$totalPriceWithDiscount,
            'formPromoCode'=>$formPromoCode->createView(),
        ]);
    }
}
