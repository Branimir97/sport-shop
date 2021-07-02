<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemListController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/list/items",
     *     "hr": "/lista/artikala"
     * }, name="items_list")
     */
    public function index(Request $request, ItemRepository $itemRepository,
                          TranslatorInterface $translator): Response
    {
        $categoriesRequest = $request->get
            ($translator->trans('categories', [], 'navigation'));
        $categories = explode(',', $categoriesRequest);
        $items = $itemRepository->findByCategories($categories);
        return $this->render('item_list/index.html.twig', [
            'items' => $items,
            'categories' => $categories
        ]);
    }
}
