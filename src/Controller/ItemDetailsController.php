<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemDetailsController extends AbstractController
{
    /**
     * @Route("/item/details", name="item_details")
     */
    public function index(): Response
    {
        return $this->render('item_details/index.html.twig', [
            'controller_name' => 'ItemDetailsController',
        ]);
    }
}
