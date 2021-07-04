<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Knp\Component\Pager\PaginatorInterface;
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
                          TranslatorInterface $translator,
                          PaginatorInterface $paginator): Response
    {
        $categoriesRequest = $request->get
            ($translator->trans('categories', [], 'navigation'));
        $categories = explode(',', $categoriesRequest);
        $itemsQuery = $itemRepository->findByCategories($categories);
        $pagination = $paginator->paginate(
            $itemsQuery,
            $request->query->getInt('page', 1),
            16
        );
        return $this->render('item_list/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories
        ]);
    }
}
