<?php

namespace App\Controller;

use App\Repository\ActionCategoryRepository;
use App\Repository\ActionItemRepository;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpecialOfferController extends AbstractController
{
    /**
     * @Route("/akcijska/ponuda", name="special_offer")
     */
    public function index(ActionItemRepository $actionItemRepository,
                          ItemRepository $itemRepository): Response
    {

        $items = [];
        $discounts = [];
        $actionItems = $actionItemRepository->findAll();
        $itemsDb = $itemRepository->findAll();
        foreach($actionItems as $actionItem) {
            array_push($items, $actionItem->getItem());
            array_push($discounts, $actionItem->getDiscountPercentage());
        }
        foreach ($itemsDb as $item) {
            $itemCategories = $item->getItemCategories();
            foreach($itemCategories as $itemCategory) {
                if(!is_null($itemCategory->getCategory()->getActionCategory())) {
                    array_push($items, $item);
                    array_push($discounts, $itemCategory->getCategory()->getActionCategory()
                        ->getDiscountPercentage());
                }
            }
        }

        return $this->render('special_offer/index.html.twig', [
            'items'=>$items,
            'discounts'=>$discounts
        ]);
    }
}
