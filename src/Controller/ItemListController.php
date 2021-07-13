<?php

namespace App\Controller;

use App\Repository\ActionItemRepository;
use App\Repository\ItemCategoryRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
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
                          ActionItemRepository $actionItemRepository,
                          ItemCategoryRepository $itemCategoryRepository,
                          PaginatorInterface $paginator): Response
    {
        $locale = $request->getLocale();
        $categoriesRequest = $request->get
            ($translator->trans('categories', [], 'navigation'));
        $categories = explode(',', $categoriesRequest);
        $itemsQuery = $itemRepository->findByCategoriesQuery($categories);

//        $results = $itemsQuery->getQuery()->getArrayResult();
//        $actions = [];
//        foreach($results as $item) {
//            foreach($actionItemRepository->findAll() as $actionItem) {
//                if($item == $actionItem->getItem()) {
//                    $actions[$item->getId()] = $actionItem->getDiscountPercentage();
//                }
//            }
//        }
//
//
//            foreach ($itemCategoryRepository->findAll() as $itemCategory) {
//                if(!is_null($itemCategory->getCategory()->getActionCategory())) {
//                    foreach($results as $item) {
//                    if($item == $itemCategory->getItem()) {
//                        $actions[$item->getId()] = $itemCategory->
//                        getCategory()->getActionCategory()->getDiscountPercentage();
//                    }
//                }
//            }
//        }
        //dodati action badgeve

        $pagination = $paginator->paginate(
          $itemsQuery->getQuery()
              ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
              ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER,
                  'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'),
          $request->query->getInt('page', 1),
          16
        );
        return $this->render('item_list/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
//            'actions' => $actions
        ]);
    }
}