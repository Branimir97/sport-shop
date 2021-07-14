<?php

namespace App\Controller;

use App\Repository\ActionItemRepository;
use App\Repository\ItemRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpecialOfferController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/special/offer",
     *     "hr": "/akcijska/ponuda"
     * }, name="special_offer")
     */
    public function index(Request $request,
                          ActionItemRepository $actionItemRepository,
                          ItemRepository $itemRepository,
                          PaginatorInterface $paginator): Response
    {
        $discounts = [];

        $actionItems = $itemRepository->getActionsOnItemsQuery();
        $actionCategoriesItems = $itemRepository->getCategoryActionsQuery();
        $actionItemsCount = count($actionItems->getQuery()->getArrayResult());
        $actionCategoriesCount = count($actionCategoriesItems->getQuery()->getArrayResult());

            if($actionItemsCount !== 0) {
                $pagination = $paginator->paginate(
                    $actionItems,
                    $request->query->getInt('page', 1),
                    3
                );
                $actionItems = $actionItemRepository->findAll();
                foreach ($actionItems as $actionItem) {
                    array_push($discounts, $actionItem->getDiscountPercentage());
                }
            }


            if ($actionCategoriesCount !== 0) {
                $pagination = $paginator->paginate(
                    $actionCategoriesItems,
                    $request->query->getInt('page', 1),
                    16
                );

                foreach ($actionCategoriesItems->getQuery()->getResult() as $item) {
                    $itemCategories = $item->getItemCategories();
                    foreach ($itemCategories as $itemCategory) {
                        if (!is_null($itemCategory->getCategory()->getActionCategory())) {
                            array_push($discounts, $itemCategory->getCategory()->getActionCategory()
                                ->getDiscountPercentage());
                        }
                    }
                }
            }

        //napraviti provjeru ako postoje i kategorije i artikli na akciji

        return $this->render('special_offer/index.html.twig', [
            'items' => $pagination,
            'discounts' => $discounts
        ]);
    }
}
