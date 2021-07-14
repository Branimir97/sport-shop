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
        $actionItems = $actionItemRepository->findAll();
        $actionItemsQuery = $itemRepository->getActionItemsQuery();
        $pagination = $paginator->paginate(
            $actionItemsQuery,
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('special_offer/index.html.twig', [
            'actionItems' => $actionItems,
            'pagination' => $pagination,
        ]);
    }
}
