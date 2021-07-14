<?php

namespace App\Controller;

use App\Entity\UserSearch;
use App\Repository\ItemRepository;
use App\Repository\UserSearchRepository;
use Knp\Component\Pager\PaginatorInterface;
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
                          UserSearchRepository $userSearchRepository,
                          PaginatorInterface $paginator): Response
    {
        $searchKeyword = $request->get('keyword');
        $searchResults = $itemRepository->searchByCipherAndName($searchKeyword, $request->getLocale());
        if(count($searchResults->getQuery()->getArrayResult()) > 0 && $searchKeyword !== null) {
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

        $pagination = $paginator->paginate(
            $searchResults,
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('search/index.html.twig', [
            'searchKeyword' => $searchKeyword,
            'pagination' => $pagination,
        ]);
    }
}
