<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @IsGranted("ROLE_ADMIN")
 */
class ActionsController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/actions",
     *     "hr": "/akcije"
     * }, name="actions")
     */
    public function index(): Response
    {
        return $this->render('actions/index.html.twig');
    }
}
