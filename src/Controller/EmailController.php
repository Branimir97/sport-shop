<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends AbstractController
{
    /**
     * @Route("/homepage/email", name="email")
     * @throws TransportExceptionInterface
     */
    public function index(MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
            ->from('sport-shop@gmail.com')
            ->to('branimirb51@gmail.com')
            ->subject('Uspješna registracija')
            ->context([
                    'name' => $this->getUser()->getFullName()
            ]

            )
            ->htmlTemplate('email/new_user.html.twig');
             $mailer->send($email);
             return $this->redirectToRoute('homepage');
    }
}
