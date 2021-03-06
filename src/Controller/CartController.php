<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/cart",
 *     "hr": "/košarica"
 * })
 * @IsGranted("ROLE_USER")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        $cart = $user->getCart();
        $totalPrice = 0;
        if($cart != null) {
            foreach($cart->getCartItems() as $cartItem) {
                $totalPrice+=$cartItem->getItem()->getPrice()*$cartItem->getQuantity();
            }
        }
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'totalPrice' => $totalPrice
        ]);
    }

    /**
     * @Route({
     *     "en": "/item/{id}",
     *     "hr": "/artikl/{id}"
     * }, name="cart_item_delete", methods={"DELETE"})
     */
    public function deleteItem(Request $request, CartItem $cartItem,
                               CartRepository $cartRepository,
                               CartItemRepository $cartItemRepository,
                               TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cartItem->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cartItem);

            $this->addFlash('danger',
                $translator->trans('flash_message.item_deleted',
                    [
                        '%item_title%' => $cartItem->getItem()->getTitle()
                    ], 'cart'));
            $entityManager->flush();
        }

        $cart = $cartRepository->findOneBy(['user' => $this->getUser()]);
        $cartItems = $cartItemRepository->findBy(['cart' => $cart]);
        if (count($cartItems) == 0) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cart);
            $entityManager->flush();
        }
        return $this->redirectToRoute('cart_index');
    }
}
