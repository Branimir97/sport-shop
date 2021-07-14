<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutUsController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/about/us",
     *     "hr": "/o/nama"
     * }, name="about_us")
     */
    public function index(): Response
    {
        return $this->render('about_us/index.html.twig');
    }
}
