<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Entity\OrderList;
use App\Entity\OrderListItem;
use App\Entity\PromoCodeUser;
use App\Form\CheckoutType;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\DeliveryAddressRepository;
use App\Repository\PromoCodeRepository;
use App\Repository\PromoCodeUserRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class CheckoutController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/billing",
     *     "hr": "/plaćanje"
     * }, name="checkout")
     */
    public function index(Request $request, CartRepository $cartRepository,
                          CartItemRepository $cartItemRepository,
                          DeliveryAddressRepository $deliveryAddressRepository,
                          UserRepository $userRepository,
                          TranslatorInterface $translator,
                          MailerInterface $mailer): Response
    {
        $session = $this->get('session');
        $totalPrice = 0;
        $discount = 0;
        $entityManager = $this->getDoctrine()->getManager();
        $cart = $cartRepository->findOneBy(['user' => $this->getUser()]);
        $cartItems = $cartItemRepository->findBy(['cart' => $cart]);
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
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        $loyaltyCard = $user->getLoyaltyCard();
        if(!is_null($loyaltyCard)) {
            $loyaltyCardCredits = $loyaltyCard->getCredits();
        } else {
            $loyaltyCardCredits = null;
        }
        if($session->get('discountWithUsedCredits') !== null) {
            $discount+=$session->get('discountWithUsedCredits');
        }
        if($session->get('discountWithUsedPromoCode') !== null) {
            $discount+=$session->get('discountWithUsedPromoCode');
        }
        if($totalPrice<$discount) {
            $totalPriceWithDiscount = 0;
        } else {
            $totalPriceWithDiscount = $totalPrice-$discount;
        }

        $creditsEarned = floor($totalPriceWithDiscount/10);

        $formCheckout = $this->createForm(CheckoutType::class, null,
            ['activeUserAddresses' => $userAddressesWithData]);

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
            if(strlen($cardNumber) < 16  || strlen($cardCVV) < 3 || $cardExpiration < $now) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.card_error',
                        [], 'checkout'));
                return $this->redirectToRoute('checkout');
            }
            $orderListItem = null;
            foreach($cartItems as $cartItem) {
                if($orderListItem == null) {
                    $orderListItem = new OrderListItem();
                }
                $orderListItem->setOrderList($orderList);
                $orderItem = new OrderItem();
                $orderItem->setItemTitle($cartItem->getItem()->getTitle());
                $orderItem->setSize($cartItem->getSize()->getValue());
                $orderItem->setColor($cartItem->getColor()->getName());
                $orderItem->setQuantity($cartItem->getQuantity());
                $orderItem->setPrice($cartItem->getItem()->getPrice());
                $entityManager->persist($orderItem);
                $orderListItem->addOrderItem($orderItem);
                $orderListItem->setDeliveryAddress(
                    $deliveryAddress->getStreet().' | '.
                    $deliveryAddress->getPostalCode().', '.
                    $deliveryAddress->getCity().' | '.
                    $deliveryAddress->getCountry()
                );
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

            $subject = $translator->trans('order_successful.subject',
                [], 'email');
            $receiverEmail = $this->getUser()->getEmail();
            $email = (new TemplatedEmail())
                ->to($receiverEmail)
                ->subject($subject)
                ->context([
                    'orderNumber' => $orderListItem->getId(),
                    'user' => $this->getUser()->getFullName(),
                    'createdAt' => $orderListItem->getCreatedAt(),
                    'totalPriceWithoutDiscount' => $orderListItem->getPriceWithoutDiscount(),
                    'totalPriceWithDiscount' => $orderListItem->getTotalPrice(),
                    'deliveryAddress' => $orderListItem->getDeliveryAddress(),
                    'cartItems' => $cartItems
                ])
                ->htmlTemplate('email/order_successful.html.twig');
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $exception) {}

            $this->addFlash('success',
                $translator->trans('flash_message.payment_success',
                    [], 'checkout'));
            return $this->redirectToRoute('order_list');
        }

        $session->set('totalPrice', $totalPrice);
        return $this->render('checkout/index.html.twig', [
            'cartItems' => $cartItems,
            'formCheckout' => $formCheckout->createView(),
            'loyaltyCardCredits' => $loyaltyCardCredits,
            'creditsEarned' => $creditsEarned,
            'totalPrice' => $totalPrice,
            'discount' => $discount,
            'totalPriceWithDiscount' => $totalPriceWithDiscount,
        ]);
    }


    /**
     * @Route({
     *     "en": "/promo/code/checker",
     *     "hr": "/promo/kod/provjera"
     * }, name="promo_code_checker")
     */
    public function promoCodeChecker(Request $request, TranslatorInterface $translator,
                                     PromoCodeRepository $promoCodeRepository,
                                     PromoCodeUserRepository $promoCodeUserRepository): RedirectResponse
    {
        $session = $this->get('session');
        $totalPrice = $session->get('totalPrice');
        $code = $request->get('code');
        $promoCodeObj = $promoCodeRepository->findOneBy(['code' => $code]);
        $promoCodeUserObj = $promoCodeUserRepository->findOneBy(
            ['promoCode' => $promoCodeObj, 'user' => $this->getUser()]);
        if(is_null($promoCodeObj) || $promoCodeObj->getStatus() == 'ISTEKAO'
                && !is_null($promoCodeUserObj)) {
            $this->addFlash('danger',
                $translator->trans('flash_message.used_promo_code',
                    [], 'checkout'));
        } else {
            $promoCodeUser = new PromoCodeUser();
            $promoCodeUser->setPromoCode($promoCodeObj);
            $promoCodeUser->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promoCodeUser);
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.promo_code_accepted',
                    [
                        '%promoCode%' => $promoCodeObj->getCode(),
                        '%promoCodeDiscountPercentage%' => $promoCodeObj->getDiscountPercentage()
                    ], 'checkout'));
            $session->set('discountWithUsedPromoCode',
                $totalPrice*($promoCodeObj->getDiscountPercentage()/100));
            $session->set('promoCodeDiscount', $promoCodeObj->getDiscountPercentage());
            $session->set('promoCode', $promoCodeObj->getCode());
        }
        return $this->redirectToRoute('checkout');

    }

    /**
     * @Route({
     *     "en": "/credits/checker",
     *     "hr": "/bodovi/provjera"
     * }, name="credits_checker")
     */
    public function creditsChecker(UserRepository $userRepository,
                                   TranslatorInterface $translator): RedirectResponse
    {
        $session = $this->get('session');
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        $loyaltyCardCredits = $user->getLoyaltyCard()->getCredits();
        $session->set('discountWithUsedCredits', $loyaltyCardCredits);
        $user->getLoyaltyCard()->setCredits(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success',
            $translator->trans('flash_message.price_reduced_loyalty_credits',
                [], 'checkout'));
        return $this->redirectToRoute('checkout');
    }
}
