<?php

namespace App\Controller;

use App\Entity\WishList;
use App\Entity\WishListItem;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Repository\WishListItemRepository;
use App\Repository\WishListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/popis/želja")
 */
class WishListController extends AbstractController
{
    /**
     * @Route("/", name="wish_list_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $wishList = $user->getWishList();
        return $this->render('wish_list/index.html.twig', [
            'wishList' => $wishList,
        ]);
    }

    /**
     * @Route("/novi/artikl/{id}", name="wish_list_item_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserRepository $userRepository,
                        WishListItemRepository $wishListItemRepository,
                        ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        if(is_null($user->getWishList())) {
            $wishList = new WishList();
            $wishList->setUser($this->getUser());
            $entityManager->persist($wishList);
            $entityManager->flush();
        } else {
            $wishList = $user->getWishList();
        }
        $wishListItemDb = $wishListItemRepository->findOneBy(
            ['wishList'=>$wishList, 'item'=>$item,]);
        if(is_null($wishListItemDb)) {
            $wishListItem = new WishListItem();
            $wishListItem->setItem($item);
            $entityManager->persist($wishListItem);
            $wishList->addWishListItem($wishListItem);
            $entityManager->persist($wishList);
            $entityManager->flush();
        } else {
            $this->addFlash('danger',
                'Artikl je već dodan na Vašu listu želja.');
            return $this->redirectToRoute('item_details',
                ['id'=>$item->getId()]);
        }

        $this->addFlash('success',
            'Artikl "'.$item->getTitle().'" uspješno dodan na Vašu listu želja.');
        return $this->redirectToRoute('wish_list_index');
    }

    /**
     * @Route("/artikl/{id}", name="wish_list_item_delete", methods={"DELETE"})
     */
    public function deleteItem(Request $request, WishListItem $wishListItem,
                               WishListRepository $wishListRepository,
                               WishListItemRepository $wishListItemRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wishListItem->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($wishListItem);

            $this->addFlash('danger',
                'Artikl "'.$wishListItem->getItem()->getTitle().'" 
                uspješno obrisan iz liste želja.');
            $entityManager->flush();
        }

        $wishList = $wishListRepository->findOneBy(['user'=>$this->getUser()]);
        $wishListItems = $wishListItemRepository->findBy(['wishList' => $wishList]);
        if (count($wishListItems) == 0) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($wishList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wish_list_index');
    }
}
