<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoyaltyProgramController extends AbstractController
{
    /**
     * @Route("/loyalty/program/{_locale}",
     *      name="loyalty_program", defaults={"_locale"="hr"})
     */
    public function index(): Response
    {
        return $this->render('loyalty_program/index.html.twig');
    }
}
