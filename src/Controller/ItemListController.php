<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemListController extends AbstractController
{
    /**
     * @Route("/itemi/{categories}", name="item_list")
     */
    public function index(Request $request): Response
    {
        $categories = $request->get('categories');
        dd($categories);
        return $this->render('item_list/index.html.twig');
    }
}
