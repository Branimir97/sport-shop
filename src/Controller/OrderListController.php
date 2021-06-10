<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderListController extends AbstractController
{
    /**
     * @Route("/order/list", name="order_list")
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $orderList = $user->getOrderList();
        return $this->render('order_list/index.html.twig', [
            'orderList' => $orderList,
        ]);
    }
}
