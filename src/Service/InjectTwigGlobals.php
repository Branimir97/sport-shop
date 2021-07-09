<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class InjectTwigGlobals implements EventSubscriberInterface
{
    private $twig;
    private $manager;
    private $security;

    public function __construct(Environment $twig, EntityManagerInterface $manager,
                                Security $security)
    {
        $this->twig = $twig;
        $this->manager = $manager;
        $this->security = $security;
    }

    public function injectGlobals() {
        $user = $this->manager->getRepository( 'App\Entity\User' )
                ->findOneBy(['email' => $this->security->getUser()->getUsername()]);
        $cart = $user->getCart();
        if(!isset($cart)) {
            $cartItemsNumber = 0;
        } else {
            $cartItemsNumber = count($cart->getCartItems());
        }
        $this->twig->addGlobal( 'cart_items', $cartItemsNumber);
    }

    public static function getSubscribedEvents(): array
    {
        return [ KernelEvents::CONTROLLER =>  'injectGlobals' ];
    }
}