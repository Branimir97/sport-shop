<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoyaltyProgramController extends AbstractController
{
    /**
     * @Route("/loyalty/program/vjernosti", name="loyalty_program")
     */
    public function index(): Response
    {
        return $this->render('loyalty_program/index.html.twig');
    }
}
