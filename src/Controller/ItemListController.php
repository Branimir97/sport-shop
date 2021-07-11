<?php

namespace App\Controller;

use App\Repository\ActionCategoryRepository;
use App\Repository\ActionItemRepository;
use App\Repository\CategoryRepository;
use App\Repository\ItemCategoryRepository;
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
                          TranslatorInterface $translator,
                          ActionItemRepository $actionItemRepository, ItemCategoryRepository $itemCategoryRepository): Response
    {
        $locale = $request->getLocale();
        $categoriesRequest = $request->get
            ($translator->trans('categories', [], 'navigation'));
        $categories = explode(',', $categoriesRequest);
        $itemsQuery = $itemRepository->findByCategories($categories, $locale);

        $actions = [];
        foreach($itemsQuery as $item) {
            foreach($actionItemRepository->findAll() as $actionItem) {
                if($item == $actionItem->getItem()) {
                    $actions[$item->getId()] = $actionItem->getDiscountPercentage();
                }
            }
        }

        foreach($itemsQuery as $item) {
            foreach ($itemCategoryRepository->findAll() as $itemCategory) {
                if(!is_null($itemCategory->getCategory()->getActionCategory())) {
                    if($item == $itemCategory->getItem()) {
                        $actions[$item->getId()] = $itemCategory->getCategory()->getActionCategory()->getDiscountPercentage();
                    }
                }
            }
        }

        return $this->render('item_list/index.html.twig', [
            'items' => $itemsQuery,
            'categories' => $categories,
            'actions' => $actions
        ]);
    }
}