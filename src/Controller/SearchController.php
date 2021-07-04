<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/search/results",
     *     "hr": "/rezultati/pretrage"
     * }, name="search")
     */
    public function index(Request $request, ItemRepository $itemRepository): Response
    {
        $searchKeyword = $request->get('search');
        $searchResults = $itemRepository->searchByCipherAndName($searchKeyword);
        return $this->render('search/index.html.twig', [
            'searchKeyword' => $searchKeyword,
            'searchResults' => $searchResults,
        ]);
    }
}
