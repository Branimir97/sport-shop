<?php

namespace App\Controller;

use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(CartRepository $cartRepository, CartItemRepository $cartItemRepository): Response
    {
        $cart = $cartRepository->findOneBy(['user'=>$this->getUser()]);
        $cartItems = $cartItemRepository->findBy(['cart'=>$cart]);
        $totalPrice = 0;
        foreach($cart->getCartItems() as $cartItem) {
            $totalPrice+=$cartItem->getItem()->getPrice()*$cartItem->getQuantity();
        }
        return $this->render('checkout/index.html.twig', [
            'cartItems'=>$cartItems,
            'totalPrice'=>$totalPrice
        ]);
    }
}
