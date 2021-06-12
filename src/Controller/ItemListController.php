<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemListController extends AbstractController
{
    /**
     * @Route("/lista/artikala", name="item_list")
     */
    public function index(): Response
    {
        return $this->render('item_list/index.html.twig');
    }
}
