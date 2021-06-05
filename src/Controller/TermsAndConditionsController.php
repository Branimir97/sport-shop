<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TermsAndConditionsController extends AbstractController
{
    /**
     * @Route("/uvjeti/i/odredbe", name="terms_and_conditions")
     */
    public function index(): Response
    {
        return $this->render('terms_and_conditions/index.html.twig');
    }
}
