<?php

namespace App\Controller;

use App\Repository\OrderItemRepository;
use App\Repository\OrderListItemRepository;
use App\Repository\OrderListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OrderListController
 * @package App\Controller
 * @Route("/narudžbe")
 */

class OrderListController extends AbstractController
{
    /**
     * @Route("/", name="order_list")
     */
    public function index(UserRepository $userRepository,
                          OrderListRepository $orderListRepository,
                          OrderListItemRepository $orderListItemRepository): Response
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUsername()]);
        $orderList = $orderListRepository->findOneBy(['user'=>$user]);
        $orderListItems = $orderListItemRepository->findBy(['orderList'=>$orderList], ['id'=>'DESC']);
        return $this->render('order_list/index.html.twig', [
            'orderListItems' => $orderListItems,
        ]);
    }

    /**
     * @Route("/{id}", name="order_details")
     */
    public function details(Request $request,
                            OrderListItemRepository $orderListItemRepository,
                            OrderItemRepository $orderItemRepository): Response
    {
        $orderListItem = $orderListItemRepository->findOneBy(['id'=>$request->get('id')]);
        $orderItems = $orderItemRepository->findBy(['orderListItem'=>$orderListItem]);

        return $this->render('order_list/details.html.twig', [
            'orderListItem'=>$orderListItem,
            'orderItems' => $orderItems,
        ]);
    }
}
