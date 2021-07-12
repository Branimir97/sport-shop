<?php

namespace App\Controller;

use App\Entity\UserSearch;
use App\Repository\ItemRepository;
use App\Repository\UserSearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/search/results/",
     *     "hr": "/rezultati/pretrage/"
     * }, name="search")
     */
    public function index(Request $request, ItemRepository $itemRepository,
                          UserSearchRepository $userSearchRepository): Response
    {
        $searchKeyword = $request->get('keyword');
        $searchResults = $itemRepository->searchByCipherAndName($searchKeyword);
        if(count($searchResults) > 0 && $searchKeyword !== null) {
            if($this->getUser()) {
                if(is_null($userSearchRepository->findOneBy(
                    ['user' => $this->getUser(), 'keyword' => $searchKeyword]))) {
                    $userSearch = new UserSearch();
                    $userSearch->setUser($this->getUser());
                    $userSearch->setKeyword($searchKeyword);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($userSearch);
                    $entityManager->flush();
                }
            }
        }

        //napraviti pagination | dodati ispravne badgeve
        return $this->render('search/index.html.twig', [
            'searchKeyword' => $searchKeyword,
            'searchResults' => $searchResults,
        ]);
    }
}
