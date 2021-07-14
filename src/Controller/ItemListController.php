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
                          PaginatorInterface $paginator,
                          ActionItemRepository $actionItemRepository): Response
    {
        $locale = $request->getLocale();
        $categoriesRequest = $request->get
            ($translator->trans('categories', [], 'navigation'));
        $categories = explode(',', $categoriesRequest);
        $itemsQuery = $itemRepository->findByCategoriesQuery($categories);

        $actionItems = $actionItemRepository->findAll();

        $pagination = $paginator->paginate(
          $itemsQuery->getQuery()
              ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
              ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER,
                  'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'),
          $request->query->getInt('page', 1),
          16
        );
        return $this->render('item_list/index.html.twig', [
            'actionItems' => $actionItems,
            'pagination' => $pagination,
            'categories' => $categories,
        ]);
    }
}