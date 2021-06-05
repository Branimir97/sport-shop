<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentAndSecurityController extends AbstractController
{
    /**
     * @Route("/nacini/placanja", name="payment_and_security")
     */
    public function index(): Response
    {
        return $this->render('payment_and_security/index.html.twig');
    }
}
