<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\UserSearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(ItemRepository $itemRepository, UserSearchRepository $userSearchRepository): Response
    {
        $suggestedItems = [];
        $suggestedItemIds = [];
        $randomSuggestedItems = [];
        if($this->getUser()) {
            $userSearch = $userSearchRepository->findOneBy(['user' => 4], ['id' => 'DESC']);
            $suggestedItems = $itemRepository->findSuggestedItems($this->getUser()->getGender(), $userSearch);

        }

        if(!is_null($suggestedItems)) {
            foreach($suggestedItems as $suggestedItem) {
                foreach($suggestedItem as $id) {
                    $suggestedItemIds[$id] = $id;
                }
            }
            $randomSuggestedItemIds = array_rand($suggestedItemIds, 4);
            foreach($randomSuggestedItemIds as $id) {
                array_push($randomSuggestedItems, $itemRepository->findOneBy(['id' => $id]));
            }
        }

        return $this->render('homepage/index.html.twig', [
            'suggestedItems' => $randomSuggestedItems
        ]);
    }
}
