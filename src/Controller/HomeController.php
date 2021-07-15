<?php

namespace App\Controller;

use App\Repository\ItemRepository;
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
    public function index(ItemRepository $itemRepository): Response
    {
        $suggestedItems = [];
        $suggestedItemIds = [];
        $randomSuggestedItems = [];
        if($this->getUser()) {
            $suggestedItems = $itemRepository->findSuggestedItems($this->getUser()->getGender());
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
