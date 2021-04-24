<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemsListAndDetailsController extends AbstractController
{
    /**
     * @Route("/items/list/and/details", name="items_list_and_details")
     */
    public function index(): Response
    {
        return $this->render('items_list_and_details/index.html.twig', [
            'controller_name' => 'ItemsListAndDetailsController',
        ]);
    }

    /**
     * @Route("/{category}", name="item_list", methods={"GET"})
     */
    public function list(Request $request, ItemRepository $itemRepository): Response
    {
        $category = $request->get('category');
        return $this->render('item/list.html.twig', [
            'category'=>$category,
            'items' => $itemRepository->findByCategoryName(($category))
        ]);
    }

    /**
     * @Route("/{id}/details", name="item_details", methods={"GET"})
     */
    public function details(Request $request, ItemRepository $itemRepository): Response
    {
        return $this->render('item/details.html.twig', [
            'item' => $itemRepository->findOneBy(['id'=>$request->get('id')]),
        ]);
    }
}
