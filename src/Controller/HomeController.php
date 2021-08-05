<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\UserProminentCategoryRepository;
use App\Repository\UserSearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/homepage",
     *     "hr": "/"
     * }, name="homepage")
     */
    public function index(Request $request,
                          ItemRepository $itemRepository,
                          UserSearchRepository $userSearchRepository,
                          UserProminentCategoryRepository $userProminentCategoryRepository): Response
    {
        $suggestedItems = [];
        $suggestedItemIds = [];
        $randomSuggestedItems = [];
        $categories = [];
        if($this->getUser()) {
            $userSearch = $userSearchRepository->findOneBy(
                ['user' => $this->getUser()], ['id' => 'DESC']);
            if(!is_null($userSearch)) {
                $searchResults = $itemRepository
                    ->searchByCipherAndName($userSearch->getKeyword(), $request->getLocale());

                foreach($searchResults->getQuery()->getResult() as $item) {
                    $itemCategories = $item->getItemCategories();
                    foreach($itemCategories as $itemCategory) {
                        array_push($categories, $itemCategory->getCategory()->getName());
                    }
                }
               $categories = array_values(array_unique($categories));
            }
            $suggestedItems = $itemRepository
                ->findSuggestedItems($this->getUser()->getGender(), $categories, $request->getLocale());
        }

        if(!is_null($suggestedItems)) {
            foreach($suggestedItems as $suggestedItem) {
                foreach($suggestedItem as $id) {
                    $suggestedItemIds[$id] = $id;
                }
            }
            if(count($suggestedItemIds) > 0) {
                if(count($suggestedItemIds) >= 4) {
                    $randomSuggestedItemIds =
                        array_rand($suggestedItemIds, 4);
                } else {
                    $randomSuggestedItemIds =
                        array_rand($suggestedItemIds, count($suggestedItemIds));
                }
                if((count($suggestedItemIds)) == 1) {
                    array_push($randomSuggestedItems,
                        $itemRepository->findOneBy(['id' => $randomSuggestedItemIds]));
                } else {
                    foreach($randomSuggestedItemIds as $id) {
                        array_push($randomSuggestedItems,
                            $itemRepository->findOneBy(['id' => $id]));
                    }
                }
            }
        }

        $prominentCategories = [];
        $prominentCategoriesDb = $userProminentCategoryRepository->findBy(['user' => $this->getUser()]);
        foreach($prominentCategoriesDb as $prominentCategory) {
            array_push($prominentCategories, $prominentCategory->getCategory());
        }

        return $this->render('homepage/index.html.twig', [
            'suggestedItems' => $randomSuggestedItems,
            'prominentCategories' => $prominentCategories
        ]);
    }
}
