<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Entity\OrderList;
use App\Entity\OrderListItem;
use App\Entity\PromoCodeUser;
use App\Form\CheckoutType;
use App\Form\PromoCodeUserType;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\DeliveryAddressRepository;
use App\Repository\PromoCodeRepository;
use App\Repository\PromoCodeUserRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/plaćanje", name="checkout")
     */
    public function index(Request $request, CartRepository $cartRepository,
                          CartItemRepository $cartItemRepository,
                          DeliveryAddressRepository $deliveryAddressRepository,
                          PromoCodeRepository $promoCodeRepository,
                          PromoCodeUserRepository $promoCodeUserRepository,
                          UserRepository $userRepository): Response
    {
        $totalPrice = 0;
        $discount = 0;
        $entityManager = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $cart = $cartRepository->findOneBy(['user'=>$this->getUser()]);
        $cartItems = $cartItemRepository->findBy(['cart'=>$cart]);
        $activeUserAddresses = $deliveryAddressRepository->findBy(['user'=>$this->getUser()]);
        $userAddressesWithData = [];
        foreach($activeUserAddresses as $activeUserAddress) {
            $userAddressesWithData[$activeUserAddress->getStreet().', '.
                                    $activeUserAddress->getPostalCode().' '.
                                    $activeUserAddress->getCity().' | '.
                                    $activeUserAddress->getCountry()]
                = $activeUserAddress;
        }
        foreach($cartItems as $cartItem) {
            $totalPrice+=$cartItem->getItem()->getPrice()*$cartItem->getQuantity();
            if($cartItem->getItem()->getActionItem()) {
                $discount+=($cartItem->getItem()->getActionItem()->getDiscountPercentage()/100)*
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
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $loyaltyCard = $user->getLoyaltyCard();
        if(!is_null($loyaltyCard)) {
            $loyaltyCardCredits = $loyaltyCard->getCredits();
        } else {
            $loyaltyCardCredits = null;
        }
        $promoCodeUser = new PromoCodeUser();
        $formPromoCode = $this->createForm(PromoCodeUserType::class, null,
            ['loyaltyCardCredits'=>$loyaltyCardCredits]);
        $formPromoCode->handleRequest($request);
        if ($formPromoCode->isSubmitted() && $formPromoCode->isValid()) {
            $promoCodeObj = $promoCodeRepository->findOneBy(
                ['code'=>$formPromoCode->get('code')->getData()]);
            $promoCodeUserObj = $promoCodeUserRepository->findOneBy(
                ['promoCode'=>$promoCodeObj, 'user'=>$this->getUser()]);
            if(!is_null($loyaltyCardCredits) && $loyaltyCardCredits>0
                && !$formPromoCode->get('use_credits')->getData() &&
                is_null($promoCodeUserObj)) {
                $this->addFlash('danger',
                    'Pogreška. Potrebno je unijeti ili promo kod ili odabrati 
                    korištenje bodova s kartice ili oboje.');
                return $this->redirect($request->getUri());
            }
            else if(!is_null($loyaltyCardCredits) && $loyaltyCardCredits>0
                && $formPromoCode->get('use_credits')->getData() &&
                !is_null($promoCodeUserObj)) {
                if(!is_null($promoCodeUserObj)) {
                    $this->addFlash('danger', 'Već ste iskoristili ovaj promo kod.');
                    return $this->redirect($request->getUri());
                }
                $session->set('discountWithUsedCredits', $loyaltyCardCredits);
                $loyaltyCard->setCredits(0);
                $entityManager->persist($loyaltyCard);
                $entityManager->flush();
                $this->addFlash('success',
                    'Cijena uspješno umanjena za količinu bodova na loyalty kartici.');
                return $this->redirect($request->getUri());
            } else if((!is_null($loyaltyCardCredits) && $loyaltyCardCredits>0
                    && $formPromoCode->get('use_credits')->getData())&&is_null($promoCodeObj)) {
                $session->set('discountWithUsedCredits', $loyaltyCardCredits);
                $loyaltyCard->setCredits(0);
                $entityManager->persist($loyaltyCard);
                $entityManager->flush();
                $this->addFlash('success',
                    'Cijena uspješno umanjena za količinu bodova na loyalty kartici.');
                return $this->redirect($request->getUri());
            }
            else {
                if(!is_null($promoCodeUserObj)) {
                    $this->addFlash('danger', 'Već ste iskoristili ovaj promo kod.');
                    return $this->redirect($request->getUri());
                } else {
                    if(!is_null($loyaltyCardCredits) && $loyaltyCardCredits>0
                        && $formPromoCode->get('use_credits')->getData()) {
                        $session->set('discountWithUsedCredits', $loyaltyCardCredits);
                        $loyaltyCard->setCredits(0);
                        $entityManager->persist($loyaltyCard);
                        $entityManager->flush();
                        $this->addFlash('success',
                            'Cijena uspješno umanjena za količinu bodova na loyalty kartici.');
                    }
                    $promoCodeUser->setUser($this->getUser());
                    $promoCodeUser->setPromoCode($promoCodeObj);
                    $entityManager->persist($promoCodeUser);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Promo kod '.$promoCodeObj->getCode(). ' prihvaćen. 
                    Cijena uspješno umanjena za '.$promoCodeObj->getDiscountPercentage(). '%.');
                    $session->set('discountWithUsedPromoCode',
                        $totalPrice*($promoCodeObj->getDiscountPercentage()/100));
                    $session->set('promoCodeDiscount', $promoCodeObj->getDiscountPercentage());
                    $session->set('promoCode', $promoCodeObj->getCode());
                    return $this->redirect($request->getUri());
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
        if($totalPriceWithDiscount<300) {
            $creditsEarned = floor(($totalPriceWithDiscount+30)/10);
        } else {
            $creditsEarned = floor($totalPriceWithDiscount/10);
        }
        $formCheckout = $this->createForm(CheckoutType::class, null,
            ['activeUserAddresses'=>$userAddressesWithData]);
        $formCheckout->handleRequest($request);
        if($formCheckout->isSubmitted() && $formCheckout->isValid()) {
            if($user->getOrderList() != null) {
                $orderList = $user->getOrderList();
            } else {
                $orderList = new OrderList();
                $orderList->setUser($user);
                $entityManager->persist($orderList);
            }
            $deliveryAddress = $formCheckout->get('deliveryAddress')->getData();
            $cardNumber = $formCheckout->get('card_number')->getData();
            $cardExpiration = $formCheckout->get('card_expiration')->getData();
            $cardCVV = $formCheckout->get('card_cvv')->getData();
            $now = new \DateTime('now');
            if(strlen($cardNumber)<16  || strlen($cardCVV)<3 || $cardExpiration<$now) {
                $this->addFlash('danger',
                    'Pogreška prilikom obrade Vaše kartice, 
                    provjerite podatke i pokušajte ponovno!');
                return $this->redirectToRoute('checkout');
            }
            $orderListItem = null;
            foreach($cartItems as $cartItem) {
                if($orderListItem == null) {
                    $orderListItem = new OrderListItem();
                }
                $orderListItem->setOrderList($orderList);
                $orderItem = new OrderItem();
                $orderItem->setItem($cartItem->getItem());
                $orderItem->setSize($cartItem->getSize());
                $orderItem->setColor($cartItem->getColor());
                $orderItem->setQuantity($cartItem->getQuantity());
                $orderItem->setPrice($cartItem->getItem()->getPrice());
                $entityManager->persist($orderItem);
                $orderListItem->addOrderItem($orderItem);
                $orderListItem->setDeliveryAddress($deliveryAddress);
                $orderListItem->setDiscount($discount);
                $orderListItem->setPriceWithoutDiscount($totalPrice);
                if($totalPriceWithDiscount<300) {
                    $orderListItem->setTotalPrice($totalPriceWithDiscount + 30);
                } else {
                    $orderListItem->setTotalPrice($totalPriceWithDiscount);
                }
                $entityManager->persist($orderListItem);
                $itemColors = $cartItem->getItem()->getItemColors();
                foreach($itemColors as $itemColor){
                    if($cartItem->getColor() === $itemColor->getColor()){
                        if(($itemColor->getQuantity()-$cartItem->getQuantity())<0) {
                            $itemColor->setQuantity(0);
                        } else {
                            $itemColor->setQuantity(
                                $itemColor->getQuantity()-$cartItem->getQuantity());
                        }
                        $entityManager->persist($itemColor);
                    }
                }
                $itemSizes = $cartItem->getItem()->getItemSizes();
                foreach($itemSizes as $itemSize){
                    if($cartItem->getSize() === $itemSize->getSize()){
                        if(($itemSize->getQuantity()-$cartItem->getQuantity())<0) {
                            $itemSize->setQuantity(0);
                        } else {
                            $itemSize->setQuantity(
                                $itemSize->getQuantity()-$cartItem->getQuantity());
                        }
                        $entityManager->persist($itemSize);
                    }
                }
            }
            $entityManager->remove($cart);
            if($loyaltyCard!=null) {
                $loyaltyCard->setCredits($creditsEarned);
                $entityManager->persist($loyaltyCard);
            }
            $entityManager->flush();
            $session->clear();
            $this->addFlash('success',
                'Uspješno provedeno plaćanje. Narudžba je u procesu obrade za dostavu.');
            return $this->redirectToRoute('order_list');
        }

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
