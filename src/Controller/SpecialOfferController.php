<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpecialOfferController extends AbstractController
{
    /**
     * @Route("/akcijska/ponuda", name="special_offer")
     */
    public function index(): Response
    {
        return $this->render('special_offer/index.html.twig');
    }
}
