<?php

namespace App\Controller;

use App\Repository\OrderListItemRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminPanelController extends AbstractController
{
    /**
     * @Route("/admin/panel", name="admin_panel")
     */
    public function index(UserRepository $userRepository, OrderListItemRepository $orderListItemRepository, ReviewRepository $reviewRepository): Response
    {
        $usersNumber = 0;
        $adminsNumber = 0;
        $soldItems = 0;
        $earnings = 0;
        $users = $userRepository->findAll();
        foreach($users as $user) {
            if(in_array("ROLE_ADMIN", $user->getRoles())){
                $adminsNumber++;
            } else {
                $usersNumber++;
            }
        }

        $orderListItems = $orderListItemRepository->findAll();
        $orders = count($orderListItems);
        foreach($orderListItems as $orderListItem) {
            foreach($orderListItem->getOrderItem() as $orderItem) {
                $soldItems+=$orderItem->getQuantity();
            }
            $earnings+=$orderListItem->getTotalPrice();
        }
        return $this->render('admin_panel/index.html.twig', [
            'users'=>$usersNumber,
            'admins'=>$adminsNumber,
            'soldItems'=>$soldItems,
            'orders'=>$orders,
            'earnings'=>$earnings,
            'last10orderListItems'=>$orderListItemRepository->findBy([], ['id'=>'DESC'], 10),
            'last5registeredUsers'=>$userRepository->findBy([], ['id'=>'DESC'], 5),
            'last5reviews'=>$reviewRepository->findBy([], ['id'=>'DESC'], 5)
        ]);
    }
}
