<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cart")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $cart = $user->getCart();
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/item/{id}", name="cart_item_delete", methods={"DELETE"})
     */
    public function deleteItem(Request $request, CartItem $cartItem): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cartItem->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cartItem);

            $this->addFlash('danger', 'Artikl "'.$cartItem->getItem()->getTitle().'" uspješno obrisan iz košarice.');
            $entityManager->flush();
        }

        return $this->redirectToRoute('cart_index');
    }
}
