<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Review;
use App\Form\CartItemType;
use App\Form\ReviewType;
use App\Repository\CartItemRepository;
use App\Repository\ItemColorRepository;
use App\Repository\ItemRepository;
use App\Repository\ItemSizeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemDetailsController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/item/{id}/details",
     *     "hr": "/artikl/{id}/detalji"
     * }, name="item_details")
     */
    public function index(Request $request,
                          ItemRepository $itemRepository,
                          ItemSizeRepository $itemSizeRepository,
                          ItemColorRepository $itemColorRepository,
                          UserRepository $userRepository,
                          CartItemRepository $cartItemRepository,
                          TranslatorInterface $translator): Response
    {
        $item = $itemRepository->findOneBy(['id' => $request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $sizeChoices = [];
        $colorChoices = [];
        foreach($item->getItemSizes() as $itemSize) {
            $sizeObject = $itemSize->getSize();
            $sizeValue = $itemSize->getSize()->getValue();
            $sizeChoices[$sizeValue] = $sizeObject;
        }
        foreach($item->getItemColors() as $itemColor) {
            $colorObject = $itemColor->getColor();
            $colorValue = $itemColor->getColor()->getName();
            $colorChoices[$colorValue] = $colorObject;
        }
        $cartItem = new CartItem();
        $formCart = $this->createForm(CartItemType::class, $cartItem,
            ['sizeChoices' => $sizeChoices, 'colorChoices' => $colorChoices]);
        $formCart->handleRequest($request);
        if ($formCart->isSubmitted() && $formCart->isValid()) {
            $formCartQuantity = $formCart->get('quantity')->getData();
            $cartItemSizeObj = $itemSizeRepository->findOneBy(
                ['size' => $formCart->get('size')->getData()]);
            $cartItemColorObj = $itemColorRepository->findOneBy(
                ['color' => $formCart->get('color')->getData()]);
            if ($formCartQuantity > $cartItemSizeObj->getQuantity()) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.max_item_size_error',
                        [
                            '%size%' => $cartItemSizeObj->getSize()->getValue(),
                            '%quantity%' => $cartItemSizeObj->getQuantity(),
                        ], 'cart'));
                return $this->redirectToRoute('item_details', ['id' => $item->getId()]);
            } else if ($formCartQuantity > $cartItemColorObj->getQuantity()) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.max_item_color_error',
                        [
                            '%color%' => $cartItemColorObj->getColor()->getName(),
                            '%quantity%' => $cartItemColorObj->getQuantity(),
                        ], 'cart'));
                return $this->redirectToRoute('item_details', ['id' => $item->getId()]);
            } else {
                $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
                if (is_null($user->getCart())) {
                    $cart = new Cart();
                    $cart->setUser($this->getUser());
                    $entityManager->persist($cart);
                    $entityManager->flush();
                } else {
                    $cart = $user->getCart();
                }
                $cartItemDb = $cartItemRepository->findOneBy(['cart' => $cart,
                    'item' => $item,
                    'size' => $formCart->get('size')->getData(),
                    'color' => $formCart->get('color')->getData()
                ]);
                if (is_null($cartItemDb)) {
                    $cartItem->setItem($item);
                    $entityManager->persist($cartItem);
                    $cart->addCartItem($cartItem);
                    $entityManager->persist($cart);
                    $entityManager->flush();
                    $this->addFlash('success',
                        $translator->trans('flash_message.item_added',
                            [
                                '%item_title%' => $item->getTitle()
                            ], 'cart'));
                    return $this->redirectToRoute('cart_index');
                } else {
                    $previousQuantity = $cartItemDb->getQuantity();
                    if (($previousQuantity + $formCartQuantity) > $cartItemColorObj->getQuantity()) {
                        $this->addFlash('danger',
                            $translator->trans('flash_message.max_item_color_cart_error',
                                [
                                    '%quantity%' => $cartItemColorObj->getQuantity()-$previousQuantity
                                ], 'cart'));
                    } else if (($previousQuantity + $formCartQuantity) > $cartItemSizeObj->getQuantity()) {
                        $this->addFlash('danger',
                            $translator->trans('flash_message.max_item_size_cart_error',
                                [
                                    '%quantity%' => $cartItemSizeObj->getQuantity()-$previousQuantity
                                ], 'cart'));
                        return $this->redirectToRoute('item_details', ['id' => $item->getId()]);
                    } else {
                        $cartItemDb->setQuantity($previousQuantity + $formCartQuantity);
                        $entityManager->persist($cartItemDb);
                        $entityManager->flush();
                        $this->addFlash('success',
                            $translator->trans('flash_message.item_added',
                                [
                                    '%item_title%' => $item->getTitle()
                                ], 'cart'));
                        return $this->redirectToRoute('cart_index');
                    }
                }
            }
        }

        $review = new Review();
        $formReview = $this->createForm(ReviewType::class, $review);
        $formReview->handleRequest($request);

        if($formReview->isSubmitted() && $formReview->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $review->setUser($this->getUser());
            $review->setItem($item);
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.review_added',
                    [], 'cart'));
            return $this->redirect($request->getUri());
        }

        return $this->render('item_details/index.html.twig', [
            'item' => $item,
            'formReview' => $formReview->createView(),
            'formCart' => $formCart->createView()
        ]);
    }
}
